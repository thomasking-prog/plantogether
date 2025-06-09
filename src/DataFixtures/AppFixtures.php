<?php

namespace App\DataFixtures;

use App\Entity\Affect;
use App\Entity\Log;
use App\Entity\Participate;
use App\Entity\Priority;
use App\Entity\Project;
use App\Entity\Statut;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {}

    public function load(ObjectManager $manager): void
    {
        // --- Users ---
        $usersData = [
            ['username' => 'alice', 'firstName' => 'Alice', 'name' => 'Smith', 'email' => 'alice@example.com'],
            ['username' => 'bob', 'firstName' => 'Bob', 'name' => 'Johnson', 'email' => 'bob@example.com'],
            ['username' => 'carol', 'firstName' => 'Carol', 'name' => 'Williams', 'email' => 'carol@example.com'],
            ['username' => 'dave', 'firstName' => 'Dave', 'name' => 'Brown', 'email' => 'dave@example.com'],
        ];
        $users = [];
        foreach ($usersData as $data) {
            $user = new User();
            $user->setUsername($data['username'])
                ->setFirstname($data['firstName'])
                ->setName($data['name'])
                ->setEmail($data['email'])
                ->setRoles(['ROLE_USER']);
            // Tous avec mot de passe 'password'
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[$data['username']] = $user;
        }

        // --- Statuts ---
        $statutsLabels = ['Open', 'In Progress', 'Done'];
        $statuts = [];
        foreach ($statutsLabels as $label) {
            $s = new Statut();
            $s->setLabel($label);
            $manager->persist($s);
            $statuts[$label] = $s;
        }

        // --- Priorities ---
        $priorityLabels = ['Low', 'Medium', 'High'];
        $priorities = [];
        foreach ($priorityLabels as $label) {
            $p = new Priority();
            $p->setLabel($label);
            $manager->persist($p);
            $priorities[$label] = $p;
        }

        // --- Projects ---
        $projectsData = [
            ['label' => 'Project Mercury', 'creator' => $users['alice'], 'participants' => ['alice', 'bob']],
            ['label' => 'Project Gemini', 'creator' => $users['bob'], 'participants' => ['bob', 'carol']],
            ['label' => 'Project Apollo', 'creator' => $users['carol'], 'participants' => ['carol', 'dave']],
            ['label' => 'Project Artemis', 'creator' => $users['dave'], 'participants' => ['alice', 'dave']],
        ];
        $projects = [];

        foreach ($projectsData as $pdata) {
            $proj = new Project();
            $proj->setLabel($pdata['label'])
                ->setCreator($pdata['creator'])
                ->setStatut($statuts['Open']);
            $manager->persist($proj);
            $projects[$pdata['label']] = $proj;

            // Add participants
            foreach ($pdata['participants'] as $username) {
                $participate = new Participate();
                $participate->setMember($users[$username])
                    ->setProject($proj);
                $manager->persist($participate);
            }
        }

        // --- Tasks per project ---
        $taskSamples = [
            'Project Mercury' => [
                'Setup repository', 'Define architecture', 'Implement login', 'Setup database',
                'Create user entity', 'Implement registration', 'Add password hashing',
                'Setup CI pipeline', 'Write unit tests', 'Deploy to staging',
            ],
            'Project Gemini' => [
                'Create wireframes', 'Design database schema', 'Implement API', 'Create frontend layout',
                'Connect frontend to API', 'Setup authentication', 'Write integration tests',
                'Fix bugs', 'Optimize performance', 'Prepare documentation',
            ],
            'Project Apollo' => [
                'Project kickoff', 'Requirements gathering', 'Create prototypes', 'Develop backend',
                'Develop frontend', 'Setup monitoring', 'Write e2e tests', 'User training',
                'Release v1', 'Post-release support',
            ],
            'Project Artemis' => [
                'Market research', 'Plan milestones', 'Set up infrastructure', 'Build MVP',
                'User feedback', 'Iterate features', 'Bug fixing', 'Prepare launch',
                'Marketing campaign', 'Final release',
            ],
        ];

        foreach ($projects as $projectLabel => $project) {
            $tasksLabels = $taskSamples[$projectLabel];
            foreach ($tasksLabels as $index => $taskLabel) {
                $task = new Task();
                $task->setLabel($taskLabel)
                    ->setCreatedAt(new \DateTimeImmutable(sprintf('-%d days', 30 - $index * 2)))
                    ->setEstimatedTime(mt_rand(1, 8)) // random hours
                    ->setFormatTime('hours')
                    ->setPriority($priorities[array_rand($priorities)])
                    ->setProject($project)
                    // Statut aléatoire parmi les 3, pas toujours Open
                    ->setStatut($statuts[$statutsLabels[array_rand($statutsLabels)]]);
                $manager->persist($task);

                // Assign 1 or 2 random members from participants
                $participantsUsernames = $projectsData[array_search($projectLabel, array_column($projectsData, 'label'))]['participants'];
                shuffle($participantsUsernames);
                $assignedUsers = array_slice($participantsUsernames, 0, rand(1, 2));
                foreach ($assignedUsers as $username) {
                    $affect = new Affect();
                    $affect->setMember($users[$username])
                        ->setTask($task);
                    $manager->persist($affect);

                    // Add a log for the assignment
                    $log = new Log();
                    $log->setMember($users[$username])
                        ->setTask($task)
                        ->setAction('add-member')
                        ->setData([sprintf('Task "%s" assigned to %s', $taskLabel, $username)]);
                    $manager->persist($log);
                }
            }
        }

        $manager->flush();
    }
}
