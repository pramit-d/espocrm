<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2021 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Core\Acl\Table;

use Espo\ORM\EntityManager;

use Espo\Entities\{
    User,
    Role as RoleEntity,
};

class DefaultRoleListProvider implements RoleListProvider
{
    private $user;

    private $entityManager;

    public function __construct(User $user, EntityManager $entityManager)
    {
        $this->user = $user;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array<Role>
     */
    public function get(): array
    {
        $roleList = [];

        $userRoleList = $this->entityManager
            ->getRepository('User')
            ->getRelation($this->user, 'roles')
            ->find();

        foreach ($userRoleList as $role) {
            $roleList[] = $role;
        }

        $teamList = $this->entityManager
            ->getRepository('User')
            ->getRelation($this->user, 'teams')
            ->find();

        foreach ($teamList as $team) {
            $teamRoleList = $this->entityManager
                ->getRepository('Team')
                ->getRelation($team, 'roles')
                ->find();

            foreach ($teamRoleList as $role) {
                $roleList[] = $role;
            }
        }

        return array_map(
            function (RoleEntity $role): RoleEntityWrapper {
                return new RoleEntityWrapper($role);
            },
            $roleList
        );
    }
}
