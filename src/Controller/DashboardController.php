<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class DashboardController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication', [
            'requireIdentity' => true,
        ]);
    }

    public function index(): ?Response
    {
        $this->set([
            'identity' => $this->request->getAttribute('identity'),
            'usersCount' => $this->fetchTable('Users')->find()->count(),
            'rolesCount' => $this->fetchTable('Roles')->find()->count(),
            'profilesCount' => $this->fetchTable('Profiles')->find()->count(),
        ]);

        return null;
    }
}