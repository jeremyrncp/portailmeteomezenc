<?php

namespace App\Tests\Controller;

use App\Entity\{City, Forecast, User};
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminTest extends WebTestCase
{
    public function testAdminRequiresAuthentication(): void
    {
        $client = static::createClient();
        foreach (['/admin', '/admin/city', '/admin/forecast', '/admin/user'] as $path) {
            $client->request('GET', $path);
            self::assertResponseRedirects('/login');
        }
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        $client->submit($crawler->selectButton('Se connecter')->form([
            '_username' => 'admin',
            '_password' => 'wrong-password',
        ]));
        self::assertResponseRedirects('/login');
        $crawler = $client->followRedirect();
        self::assertSelectorExists('[role="alert"]');
        $client->submit($crawler->selectButton('Se connecter')->form([
            '_username' => 'admin', '_password' => 'test-admin-password',
        ]));
        self::assertResponseRedirects('/');
        $provider = static::getContainer()->get('security.user.provider.concrete.admin_provider');
        $admin = $provider->loadUserByIdentifier('admin');
        $hasher = static::getContainer()->get('security.password_hasher_factory')->getPasswordHasher($admin);
        self::assertTrue($hasher->verify($admin->getPassword(), 'test-admin-password'));
    }

    public function testCrudLifecycle(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schema = new SchemaTool($entityManager);
        $schema->dropSchema($metadata);
        $schema->createSchema($metadata);
        $client->loginUser(static::getContainer()->get('security.user.provider.concrete.admin_provider')->loadUserByIdentifier('admin'));

        foreach (['city', 'forecast', 'user'] as $entity) {
            $client->request('GET', '/admin/'.$entity);
            self::assertResponseIsSuccessful();
        }

        $crawler = $client->request('GET', '/admin/city/new');
        $form = $crawler->filter('form[name="City"]')->form();
        $client->submit($form, ['City[name]' => 'Le Béage', 'City[postalCode]' => '07630']);
        self::assertResponseRedirects();
        $city = static::getContainer()->get('doctrine')->getRepository(City::class)->findOneBy(['name' => 'Le Béage']);
        self::assertNotNull($city);
        $cityId = $city->getId();

        $crawler = $client->request('GET', '/admin/forecast/new');
        $client->submit($crawler->filter('form[name="Forecast"]')->form(), [
            'Forecast[foreca]' => 'Beau temps', 'Forecast[city]' => $cityId,
        ]);
        self::assertResponseRedirects();
        self::assertNotNull(static::getContainer()->get('doctrine')->getRepository(Forecast::class)->findOneBy(['foreca' => 'Beau temps']));

        $crawler = $client->request('GET', '/admin/user/new');
        $client->submit($crawler->filter('form[name="User"]')->form(), [
            'User[email]' => 'test@example.com', 'User[plainPassword]' => 'test-password', 'User[city]' => $cityId,
        ]);
        self::assertResponseRedirects();
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        self::assertNotNull($user);
        self::assertTrue(password_verify('test-password', $user->getPassword()));
        $hash = $user->getPassword();
        $crawler = $client->request('GET', '/admin/user/'.$user->getId().'/edit');
        $client->submit($crawler->filter('form[name="User"]')->form(), ['User[email]' => 'updated@example.com']);
        self::assertResponseRedirects();
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'updated@example.com']);
        self::assertSame($hash, $user->getPassword());

        $forecast = static::getContainer()->get('doctrine')->getRepository(Forecast::class)->findOneBy(['foreca' => 'Beau temps']);
        foreach (['user' => $user->getId(), 'forecast' => $forecast->getId(), 'city' => $cityId] as $entity => $id) {
            $crawler = $client->request('GET', '/admin/'.$entity.'/'.$id.'/edit');
            self::assertResponseIsSuccessful();
            $token = $crawler->filter('input[name="token"]')->attr('value');
            $client->request('POST', '/admin/'.$entity.'/'.$id.'/delete', ['token' => $token]);
            self::assertResponseRedirects();
        }
        foreach ([City::class, Forecast::class, User::class] as $entityClass) {
            self::assertSame(0, static::getContainer()->get('doctrine')->getRepository($entityClass)->count([]));
        }
    }
}
