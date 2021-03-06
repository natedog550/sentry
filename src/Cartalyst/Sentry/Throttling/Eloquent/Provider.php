<?php namespace Cartalyst\Sentry\Throttling\Eloquent;
/**
 * Part of the Sentry Package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Sentry
 * @version    2.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Cartalyst\Sentry\Throttling\ThrottleInterface;
use Cartalyst\Sentry\Throttling\ProviderInterface;
use Cartalyst\Sentry\Users\ProviderInterface as UserProviderInterface;

class Provider implements ProviderInterface {

	/**
	 * The Eloquent throttle model.
	 *
	 * @var string
	 */
	protected $model = 'Cartalyst\Sentry\Throttling\Eloquent\Throttle';

	/**
	 * The user provider used for finding users
	 * to attach throttles to.
	 *
	 * @var Cartalyst\Sentry\Users\UserInterface
	 */
	protected $userProvider;

	/**
	 * Throttling status.
	 *
	 * @var bool
	 */
	protected $enabled = true;

	/**
	 * Creates a new throttle provider.
	 *
	 * @param  Cartalyst\Sentry\Users\UserInterface  $userProvider
	 * @return void
	 */
	public function __construct(UserProviderInterface $userProvider)
	{
		$this->userProvider = $userProvider;
	}

	/**
	 * Finds a throttler by the given user ID.
	 *
	 * @param  mixed  $id
	 * @return Cartalyst\Sentry\Throttling\ThrottleInterface
	 */
	public function findByUserId($id)
	{
		$user  = $this->userProvider->findById($id);
		$model = $this->createModel();

		if ( ! $throttle = $model->where('user_id', '=', ($userId = $user->getUserId()))->first())
		{
			$throttle = $this->createModel();
			$throttle->user_id = $userId;
			$throttle->save();
		}

		return $throttle;
	}

	/**
	 * Finds a throttling interface by the given user login.
	 *
	 * @param  string  $login
	 * @return Cartalyst\Sentry\Throttling\ThrottleInterface
	 */
	public function findByUserLogin($login)
	{
		$user  = $this->userProvider->findByLogin($login);
		$model = $this->createModel();

		if ( ! $throttle = $model->where('user_id', '=', ($userId = $user->getUserId()))->first())
		{
			$throttle = $this->createModel();
			$throttle->user_id = $userId;
			$throttle->save();
		}

		return $throttle;
	}

	/**
	 * Enable throttling.
	 *
	 * @return void
	 */
	public function enable()
	{
		$this->enabled = true;
	}

	/**
	 * Disable throttling.
	 *
	 * @return void
	 */
	public function disable()
	{
		$this->enabled = false;
	}

	/**
	 * Check if throttling is enabled.
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class;
	}

}
