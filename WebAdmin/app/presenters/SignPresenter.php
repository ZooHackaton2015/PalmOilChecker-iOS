<?php

namespace App\Presenters;

use App\Model\UserManager;
use App\Model\Users;
use Components\AddUserFormFactory;
use Nette;
use Components\SignFormFactory;
use Nette\Application\UI\Form;


class SignPresenter extends BasePresenter
{
	/** @var SignFormFactory @inject */
	public $signInFormFactory;

	/** @var AddUserFormFactory @inject  */
	public $addUserFormFactory;

	/** @var Users @inject  */
	public $users;

	/** @var UserManager @inject */
	public $userManager;

	public $onSave = [];

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->redirect('Homepage:');
	}

	public function renderInit(){
		if($this->users->hasEntries()){
			$this->flashMessage('Inicializace je možna pouze pokud ještě neexistují žádní další uživatelé');
			$this->redirect('in');
		}
	}


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->signInFormFactory->create();
		$form->onSuccess[] = function ($form, $values) {
			$this->signInFormSucceeded($form, $values);
			if($this->user->isLoggedIn()){
				$this->redirect('Homepage:');
			}
		};
		return $form;
	}


	public function signInFormSucceeded(Form $form, $values)
	{
		if ($values->remember) {
			$this->user->setExpiration('14 days', FALSE);
		} else {
			$this->user->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->user->login($values->email, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('The username or password you entered is incorrect.');
		}
	}

	public function createComponentAddUserForm()
	{
		$form = $this->addUserFormFactory->create();

		$form->onSuccess[] = function($form, $values){
			$this->userManager->add($values->email, $values->password);
			$this->flashMessage('První uživatel byl úspěšně vytvořen');
			$this->redirect('in');
		};

		return $form;
	}

}
