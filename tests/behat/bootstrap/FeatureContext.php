<?php
use PaulGibbs\WordpressBehatExtension\Context\RawWordpressContext;
use PaulGibbs\WordpressBehatExtension\Context\Traits\UserAwareContextTrait;
use PaulGibbs\WordpressBehatExtension\Context\Traits\CacheAwareContextTrait;
use Behat\Mink\Exception\DriverException;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Define application features from the specific context.
 */
class FeatureContext extends RawWordpressContext {
	use UserAwareContextTrait, CacheAwareContextTrait;

	/**
	 * @BeforeStep
	 */
	public function beforeStep() {

		try {
			$this->getSession()->resizeWindow( 1440, 900, 'current' );
		} catch ( \Behat\Mink\Exception\UnsupportedDriverActionException $e ) {
			// No window, no resize.
		}
	}

	/**
	 * Initialise context.
	 *
	 * Every scenario gets its own context instance.
	 * You can also pass arbitrary arguments to the context constructor through behat.yml.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * @Then the input field :arg1 should contain :arg2
	 */
	public function theInputFieldShouldContain( $arg1, $arg2 ) {
		$this->assertSession()
		     ->elementAttributeContains( 'css', $arg1, 'value', $arg2 );
	}

	/**
	 * @Then I logout
	 */
	public function iLogOut() {
		$this->logOut();
	}

	/**
	 * @Then the textarea field :arg1 should contain :arg2
	 */
	public function theTextareaFieldShouldContain( $arg1, $arg2 ) {
		$this->assertSession()
			->elementTextContains( 'css', $arg1, $arg2 );
	}

	/**
	 * @Given I am logged in with the name :arg1 and the password :arg2
	 */
	public function iAmLoggedInWithTheNameAndThePassword( $arg1, $arg2 ) {

		if ( $this->loggedIn() ) {
			$this->logOut();
			sleep( 1 );
		}
		$this->logIn( $arg1, $arg2 );
	}

	/**
	 * @Then I should see :arg1 in :arg2
	 */
	public function iShouldSeeIn( $arg1, $arg2 ) {
		$this->assertSession()
			->elementTextContains( 'css', $arg2, $arg1 );
	}

	/**
	 * @Then I should not see :arg1 in :arg2
	 */
	public function iShouldNotSeeIn( $arg1, $arg2 ) {
		$this->assertSession()
			->elementTextNotContains( 'css', $arg2, $arg1 );
	}

	/**
	 * @Then element :arg1 should not exist
	 */
	public function elementShouldNotExist( $arg1 ) {
		$this->assertSession()
			->elementNotExists( 'css', $arg1 );
	}

	/**
	 * @Then element :arg1 should exist
	 */
	public function elementShouldExist( $arg1 ) {
		$this->assertSession()
			->elementExists( 'css', $arg1 );
	}

	/**
	 * @Then I wait :seconds seconds
	 */
	public function iWaitSeconds( $seconds ) {
		$this->getSession()->wait( $seconds * 1000 );
	}

	/**
	 * @Then I should only see :arg1 :arg2 elements in :arg3
	 */
	public function iShouldOnlySeeElementIn( $arg1, $arg2, $arg3 ) {

		$element = $this->getSession()->getPage()->find( 'css', $arg3 );
		$found   = count( $element->findAll( 'css', $arg2 ) );
		if ( $found !== (int) $arg1 ) {
			throw new \Exception( sprintf( '%d elements of %s in %s where found. %d expected.', $found, $arg2, $arg3, $arg1 ) );
		}
	}

	/**
	 * @Then :value should be selected in :element
	 */
	public function shouldBeSelectedIn( $value, $selector ) {

		$element = $this->getSession()->getPage()->find( 'css', $selector );
		if ( ! $element ) {
			throw new \Exception(
				sprintf(
					'Element %s not found.', $selector
				)
			);
		}

		if ( $element->getValue() !== $value ) {
			throw new \Exception(
				sprintf(
					'The expected value for %s was %s. %s is the actual value', $selector, $value, $element->getValue()
				)
			);
		}
	}

}
