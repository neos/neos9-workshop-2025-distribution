<?php
declare(strict_types=1);

namespace Neos\Demo\Form\Runtime\Action;

/*
 * This file is part of the Neos.Demo package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;
use Neos\Flow\Security\AccountFactory;
use Neos\Flow\Security\AccountRepository;
use Neos\Party\Domain\Repository\PartyRepository;
use Neos\Party\Domain\Service\PartyService;
use Neos\Neos\Domain\Model\User;
use Neos\Party\Domain\Model\PersonName;
use Neos\Fusion\Form\Runtime\Action\AbstractAction;

class CreateUserAction extends AbstractAction
{
    #[Flow\Inject]
    protected ?AccountRepository $accountRepository;

    #[Flow\Inject]
    protected ?PartyRepository $partyRepository;

    #[Flow\Inject]
    protected ?PartyService $partyService;

    #[Flow\Inject]
    protected ?AccountFactory $accountFactory;

    #[Flow\Inject]
    protected ?PersistenceManager $persistenceManager;

    /**
     * @throws ActionException|IllegalObjectTypeException
     */
    public function perform(): ?ActionResponse
    {
        $accountIdentifier = $this->options['username'];
        $password = $this->options['password'];

        $existingAccount = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($accountIdentifier, 'Neos.Neos:Backend');
        if ($existingAccount !== null) {
            throw new ActionException('Account already exists');
        }

        $firstName = ucfirst($accountIdentifier);
        $lastName = 'Demo';

        $user = new User();
        $user->setName(new PersonName('', $firstName, '', $lastName));
        $this->partyRepository->add($user);

        $account = $this->accountFactory->createAccountWithPassword($accountIdentifier, $password, $this->options['roles'], 'Neos.Neos:Backend');
        $this->partyService->assignAccountToParty($account, $user);
        $account->setExpirationDate(new \DateTime($this->options['expiry']));

        $this->accountRepository->add($account);
        $this->persistenceManager->persistAll();
        return null;
    }
}
