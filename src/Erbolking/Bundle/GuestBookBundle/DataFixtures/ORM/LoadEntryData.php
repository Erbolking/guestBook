<?php
namespace Erbolking\Bundle\GuestBookBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Erbolking\Bundle\GuestBookBundle\Entity\Entry;
use \DateTime;
use Symfony\Component\Config\Definition\Exception\Exception;

class LoadUserData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $nameCollection = array('John', 'Jerry', 'Celinda', 'Mark', 'Rhianna', 'Ursula', 'Erika', 'Elisa', 'Christine', 'Cindi');
        $imageCollection = array('user1', 'user2', 'user3', 'user4', 'user5', null);

        //extract images
        $zipFilePath = realpath(__DIR__ . '/../Data') . '/images.zip';
        $imagesPath = realpath(__DIR__ . '/../../../../../../web/uploads/images');
        $zip = new \ZipArchive();
        $extracted = false;
        $fileExist = $zip->open($zipFilePath);

        if ($fileExist === true) {
            $extracted = $zip->extractTo($imagesPath);
            $zip->close();
        }
        if (!$extracted) {
            throw new \Exception('Images from ' . $zipFilePath . ' were not uploaded into the image gallery ' . $imagesPath . '.');
        }

        /* @var $manager \Doctrine\ORM\EntityManager */
        try {
            $entryTable = $manager->getClassMetadata('ErbolkingGuestBookBundle:Entry')->getTableName();
            $result = $manager->getConnection()->query('SHOW TABLE STATUS LIKE "' . $entryTable . '"')->fetch();
            $incrementId = $result['Auto_increment'];
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        for ($i = 1; $i<= 30; $i++) {
            $entry = new Entry();
            $entry->setActive(1);
            $entry->setIpAddress('192.168.0.' . mt_rand(1, 255));
            $entry->setPublicDate(new DateTime());

            if ($i > 20) {
                /* @var $parent \Erbolking\Bundle\GuestBookBundle\Entity\Entry */
                $parent = $manager->getRepository('ErbolkingGuestBookBundle:Entry')->find(mt_rand($incrementId, $incrementId + 20));
                $entry->setParent($parent);
            }

            $imageName = $imageCollection[mt_rand(0, 5)];
            if ($imageName) {
                $entry->setImage('uploads/images/' . $imageName . '.png');
            }
            $name = $nameCollection[mt_rand(0, 9)];
            $entry->setName($name);
            $entry->setEmail(strtolower($name) . '@gmail.com');
            $entry->setMessage(strip_tags(file_get_contents('http://loripsum.net/api')));

            $manager->persist($entry);
            $manager->flush();
        }
    }
}