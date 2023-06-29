<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private static $articleImages = [
        'a21a58b2-b2ad-49e2-8d33-e97603a2848c-6450e99ec6f73.jpeg',
        'mercury.jpeg',
        'lightspeed.png',
    ];

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $users = [];
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setRoles(['ROLE_USER']);
        $password = $this->hasher->hashPassword($user, 'password');
        $user->setPassword($password);
        $user->setDescription($faker->word);
        $manager->persist($user);
        
        $user_profile = new UserProfile();
        $user_profile->setName($faker->firstName . ' ' . $faker->lastName);
        $user_profile->setBio($faker->word);
        $user_profile->setUser($user);

        $users[] = $user;

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email);
            $user->setRoles(['ROLE_USER']);
            $password = $this->hasher->hashPassword($user, 'password');
            $user->setPassword($password);
            $user->setDescription($faker->word);
            $manager->persist($user);
            $users[] = $user;

            $user_profile = new UserProfile();
            $user_profile->setName($faker->firstName . ' ' . $faker->lastName);
            $user_profile->setBio($faker->word);
            $user_profile->setUser($user);
            $manager->persist($user_profile);
        }

        foreach ($users as $user) {
            for ($i = 0; $i < 40; $i++) {

                $post = new Post();
                $post->setTitle($faker->word);
                $post->setType($faker->randomElement($post::TYPES));
                $post->setUrl($faker->word);
                $post->setFile('img/logo.png');
                $post->setUser($user);
                $post->setDescription($faker->word);
                $manager->persist($post);
            }
        }

        $manager->flush();
    }
}
