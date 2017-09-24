<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\DataFixtures\Scenarios;

use FOS\UserBundle\Model\UserManager;
use Inowas\Common\Fixtures\DataFixtureInterface;
use Inowas\Common\Id\UserId;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class LoadScenarioBase implements ContainerAwareInterface, DataFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var  UserId */
    protected $ownerId;

    /** @var  array */
    protected $userIdList;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function loadUsers(UserManager $userManager): void
    {

        $userListHeads = array('username', 'name', 'email', 'password', 'roles');
        $userList = array(
            array('guest', 'guest', 'guest@inowas.com', 'guest', []),
            array('inowas', 'inowas', 'inowas@inowas.com', '#inowas#', ['ROLE_NM_MF']),
            array('ralf.junghanns', 'Ralf Junghanns', 'ralf.junghanns@tu-dresden.de', '#inowas#', ['ROLE_NM_MF']),
            array('jana.glass', 'Jana Glass', 'jana.ringleb@tu-dresden.de', '#inowas#', ['ROLE_NM_MF']),
            array('jana.sallwey', 'Jana Sallwey', 'jana.sallwey@tu-dresden.de', '#inowas#', ['ROLE_NM_MF']),
            array('catalin.stefan', 'Catalin Stefan', 'catalin.stefan@tu-dresden.de', '#inowas#', ['ROLE_NM_MF']),
            array('martin.wudenka', 'Martin Wudenka', 'martin.wudenka@tu-dresden.de', '#inowas#', ['ROLE_NM_MF']),
            array('sandro.keil', 'Sandro Keil', 'sandro.keil@tu-dresden.de', '#inowas#', ['ROLE_NM_MF']),
            array('maike.groeschke', 'Maike Gröschke', 'maike.groeschke@bgr.de', 'mg_inowas_221', ['ROLE_NM_MF']),
            array('nawapi', 'nawapi', 'nawapi@inowas.com', 'na-ql-ww-pi', ['ROLE_NM_MF']),
            array('inowas1', 'inowas1', 'inowas1@gast', 'cheekohk', ['ROLE_NM_MF']),
            array('malena', 'malena', 'malena@gast', 'wohchoad', ['ROLE_NM_MF']),
            array('jorge', 'jorge', 'jorge@gast', 'geuwaexu', ['ROLE_NM_MF']),
            array('ahmad', 'ahmad', 'ahmad@gast', 'eipobung', ['ROLE_NM_MF']),
            array('jonas', 'jonas', 'jonas@gast', 'eyipuamo', ['ROLE_NM_MF']),
            array('guzman', 'guzman', 'guzman@gast', 'xookahbi', ['ROLE_NM_MF']),
            array('cristiano', 'cristiano', 'cristiano@gast', 'ohfohfum', ['ROLE_NM_MF']),
            array('anna', 'anna', 'anna@gast', 'ahmohnah', ['ROLE_NM_MF']),
            array('byambasuren', 'byambasuren', 'byambasuren@gast', 'aejohque', ['ROLE_NM_MF']),
            array('jeanne', 'jeanne', 'jeanne@gast', 'xiegooge', ['ROLE_NM_MF']),
            array('nguyen', 'nguyen', 'nguyen@gast', 'uvupauho', ['ROLE_NM_MF']),
            array('meng', 'meng', 'meng@gast', 'thauzeej', ['ROLE_NM_MF']),
            array('xueyan', 'xueyan', 'xueyan@gast', 'rohpooqu', ['ROLE_NM_MF']),
            array('badia', 'badia', 'badia@gast', 'caegouno', ['ROLE_NM_MF']),
            array('rodrigo', 'rodrigo', 'rodrigo@gast', 'ahdeogei', ['ROLE_NM_MF']),
            array('guillermo', 'guillermo', 'guillermo@gast', 'yeuvoolo', ['ROLE_NM_MF']),
            array('angel', 'angel', 'angel@gast', 'aezepees', ['ROLE_NM_MF']),
            array('sospeter', 'sospeter', 'sospeter@gast', 'uuchahth', ['ROLE_NM_MF']),
            array('bhesh', 'bhesh', 'bhesh@gast', 'teigotab', ['ROLE_NM_MF']),
            array('vahid', 'vahid', 'vahid@gast', 'ophiebah', ['ROLE_NM_MF']),
            array('andre', 'andre', 'andre@gast', 'ohsohshi', ['ROLE_NM_MF']),
            array('adeyinka', 'adeyinka', 'adeyinka@gast', 'aimeipae', ['ROLE_NM_MF']),
            array('muna', 'muna', 'muna@gast', 'iegheith', ['ROLE_NM_MF']),
            array('bewuket', 'bewuket', 'bewuket@gast', 'hohreith', ['ROLE_NM_MF']),
            array('mutaz', 'mutaz', 'mutaz@gast', 'chohexai', ['ROLE_NM_MF'])
        );

        foreach ($userList as $item){
            $item = array_combine($userListHeads, $item);
            $user = $userManager->findUserByUsername($item['username']);

            if (!$user) {
                // Add new User
                $user = $userManager->createUser();
                $user->setUsername($item['username']);
                $user->setName($item['name']);
                $user->setEmail($item['email']);
                $user->setPlainPassword($item['password']);
                $user->setEnabled(true);

                /** @var string $role */
                foreach ($item['roles'] as $role) {
                    $user->addRole($role);
                }

                $userManager->updateUser($user);
            }

            /** @var string $role */
            foreach ($item['roles'] as $role) {
                if (!$user->hasRole($role)) {
                    $user->addRole($role);
                    $userManager->updateUser($user);
                }
            }

            $this->userIdList[] = UserId::fromString($user->getId()->toString());
        }

        $owner = $userManager->findUserByUsername('jana.glass');
        $owner->addRole('ROLE_ADMIN');
        $userManager->updateUser($owner);
        $this->ownerId = $owner->getId()->toString();
    }

    protected function loadRowsFromCsv($filename): array {
        $header = null;
        $rows = array();
        if (($handle = fopen($filename, 'rb')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                if (null === $header) {
                    $header = $data;
                    continue;
                }

                $rows[] = array_combine($header, $data);

            }
            fclose($handle);
        }

        return $rows;
    }

    protected function loadHeaderFromCsv($filename): array
    {
        $data = array();
        if (($handle = fopen($filename, 'rb')) !== FALSE) {
            $data = fgetcsv($handle, 1000, ";");
            fclose($handle);
        }

        return $data;
    }

    protected function getDates(array $header): array{
        $dates = array();
        foreach ($header as $data){
            if (explode(':', $data)[0] === 'date'){
                $dates[] = $data;
            }
        }
        return $dates;
    }
}
