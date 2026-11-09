<?php

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\Forecast;
use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PortalTest extends WebTestCase
{
    public function testPortalRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertResponseRedirects('/login');
    }

    public function testUserLoginShowsOnlyTheirCommuneAndLatestFiveBulletins(): void
    {
        $client = static::createClient();
        $this->createFixtures();
        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Se connecter')->form([
            '_username' => 'resident@example.com', '_password' => 'resident-password',
        ]));
        self::assertResponseRedirects('/');
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Les Estables');
        self::assertSelectorTextContains('.latest-forecast', 'Bulletin 6');
        self::assertSelectorCount(4, '.history-item');
        self::assertSelectorTextContains('.history-item', 'Bulletin 5');
        self::assertStringNotContainsString('Bulletin 0', $client->getResponse()->getContent());
        self::assertStringNotContainsString('Prévision confidentielle autre commune', $client->getResponse()->getContent());
        self::assertStringNotContainsString('Bulletin sans date ancien', $client->getResponse()->getContent());
        self::assertSelectorNotExists('.forecast-text script');
        self::assertSelectorTextContains('.forecast-text', '<script>alert(1)</script>');
        self::assertSelectorExists('img[src="/logo.jpg"]');
        self::assertStringContainsString('no-store', $client->getResponse()->headers->get('Cache-Control'));
        $logoutUrl = $crawler->filter('a.logout')->attr('href');
        $client->request('GET', '/admin');
        self::assertResponseStatusCodeSame(403);
        $client->request('GET', $logoutUrl);
        self::assertResponseRedirects('/login');
        $client->request('GET', '/');
        self::assertResponseRedirects('/login');
    }

    public function testUserWithoutCommuneSeesGuidance(): void
    {
        $client = static::createClient();
        $this->createFixtures();
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'unassigned@example.com']);
        $client->loginUser($user);
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.empty-state', 'Aucune commune');
        self::assertSelectorNotExists('.latest-forecast');
    }

    public function testCommuneWithoutForecastSeesEmptyState(): void
    {
        $client = static::createClient();
        $this->createFixtures();
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'empty@example.com']);
        $client->loginUser($user);
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.empty-state', 'Aucune prévision');
    }

    public function testMemoryAdminCanStillAccessAdministration(): void
    {
        $client = static::createClient();
        $client->loginUser(static::getContainer()->get('security.user.provider.concrete.admin_provider')->loadUserByIdentifier('admin'));
        $client->request('GET', '/');
        self::assertResponseRedirects('/admin');
    }

    private function createFixtures(): void
    {
        $manager = static::getContainer()->get('doctrine')->getManager();
        $metadata = $manager->getMetadataFactory()->getAllMetadata();
        $schema = new SchemaTool($manager);
        $schema->dropSchema($metadata);
        $schema->createSchema($metadata);
        $city = (new City())->setName('Les Estables')->setPostalCode('43150');
        $otherCity = (new City())->setName('Le Béage');
        $emptyCity = (new City())->setName('Fay-sur-Lignon');
        foreach ([$city, $otherCity, $emptyCity] as $commune) {
            $manager->persist($commune);
        }
        foreach (['resident@example.com' => $city, 'unassigned@example.com' => null, 'empty@example.com' => $emptyCity] as $email => $commune) {
            $user = (new User())->setEmail($email)->setCity($commune)->setPassword(password_hash('resident-password', PASSWORD_BCRYPT));
            $manager->persist($user);
        }
        for ($i = 0; $i < 7; ++$i) {
            $forecast = (new Forecast())->setCity($city)->setCreatedAt(new \DateTime('2026-11-'.(1 + $i).' 12:00:00'))
                ->setForeca('Bulletin '.$i.($i === 6 ? "\n<script>alert(1)</script>" : ''));
            $manager->persist($forecast);
        }
        $manager->persist((new Forecast())->setCity($city)->setForeca('Bulletin sans date ancien'));
        $manager->persist((new Forecast())->setCity($otherCity)->setCreatedAt(new \DateTime('2026-12-01'))->setForeca('Prévision confidentielle autre commune'));
        $manager->flush();
    }
}
