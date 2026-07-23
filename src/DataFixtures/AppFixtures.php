<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $companyNames = ['TestCorp', 'Example Ltd', 'Acme Kft', 'Globex', 'Initech'];
        $companies = [];
        foreach ($companyNames as $name) {
            $company = new Company();
            $company->setName($name);
            $manager->persist($company);
            $companies[$name] = $company;
        }

        // company_name, rating, text, email, hours-ago (for deterministic ordering)
        $reviews = [
            ['TestCorp', 5, 'Kiváló szolgáltatás, nagyon elégedett vagyok.', 'alice@example.com', 1],
            ['TestCorp', 3, 'Közepes tapasztalat, lehetne jobb is.', 'bob@example.com', 2],
            ['Example Ltd', 4, 'Jó cég, ajánlom a figyelmükbe.', 'carol@example.com', 3],
            ['Acme Kft', 2, 'Lassú ügyfélszolgálat, de a termék rendben volt.', 'dave@example.com', 4],
            ['Globex', 5, 'Tökéletes kommunikáció és gyors szállítás.', 'eve@example.com', 5],
            ['Initech', 1, 'Nem ajánlom, sok volt a hiba.', 'frank@example.com', 6],
            ['TestCorp', 4, 'Megbízható partner, többször rendeltem.', 'grace@example.com', 7],
            ['Example Ltd', 2, 'A vártnál gyengébb minőség.', 'heidi@example.com', 8],
            ['Acme Kft', 5, 'Kiváló ár-érték arány.', 'ivan@example.com', 9],
            ['Globex', 3, 'Átlagos élmény, semmi különös.', 'judy@example.com', 10],
            ['Initech', 4, 'Jó termékek, de a szállítás csúszott.', 'karl@example.com', 11],
            ['TestCorp', 5, 'Kiváló ügyfélszolgálat, gyors válaszok.', 'leo@example.com', 12],
            ['Example Ltd', 3, 'Elfogadható, de van hova fejlődni.', 'mallory@example.com', 13],
            ['Acme Kft', 4, 'Barátságos személyzet, ajánlom.', 'nina@example.com', 14],
            ['Globex', 5, 'Legjobb tapasztalatom idén, szuper csapat.', 'oscar@example.com', 15],
        ];

        $reviewObjects = [];
        foreach ($reviews as [$companyName, $rating, $text, $email, $hoursAgo]) {
            $review = new Review();
            $review->setCompany($companies[$companyName]);
            $review->setRating($rating);
            $review->setReviewText($text);
            $review->setAuthorEmail($email);
            $manager->persist($review);
            $reviewObjects[] = [$review, $hoursAgo];
        }

        // Gedmo Timestampable stamps createdAt/updatedAt with "now" on flush;
        // !!!!! override createdAt afterwards so list ordering and pagination are deterministic. !!!!!!
        $manager->flush();

        foreach ($reviewObjects as [$review, $hoursAgo]) {
            $review->setCreatedAt(new \DateTimeImmutable(sprintf('-%d hours', $hoursAgo)));
        }

        $manager->flush();
    }
}
