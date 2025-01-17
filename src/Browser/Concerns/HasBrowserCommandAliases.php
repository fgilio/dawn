<?php

namespace Glhd\Dawn\Browser\Concerns;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Glhd\Dawn\Support\Selector;
use Illuminate\Support\Js;
use Illuminate\Support\Str;

trait HasBrowserCommandAliases
{
	public function clickLink(string $text, bool $wait = false): static
	{
		return $this->click(WebDriverBy::linkText($text), wait: $wait);
	}
	
	public function clickButton(string|WebDriverBy $selector, bool $wait = false): static
	{
		return $this->click($selector, resolver: 'resolveForButtonPress', wait: $wait);
	}
	
	public function radio(string|WebDriverBy $selector): static
	{
		return $this->clickRadio($selector);
	}
	
	public function check(string|WebDriverBy $selector): static
	{
		return $this->checkOrUncheck($selector, true);
	}
	
	public function uncheck(string|WebDriverBy $selector): static
	{
		return $this->checkOrUncheck($selector, false);
	}
	
	public function move(int $x = 0, int $y = 0): static
	{
		return $this->setPosition($x, $y);
	}
	
	public function visitRoute($name, $parameters = [], $absolute = true): static
	{
		return $this->visit(route($name, $parameters, $absolute));
	}
	
	public function script(string|array $scripts): static
	{
		return $this->executeScript($scripts);
	}
	
	public function keys(string|WebDriverBy $selector, ...$keys): static
	{
		return $this->sendKeys($selector, $keys);
	}
	
	public function type(WebDriverBy|string $selector, string $keys): static
	{
		return $this->sendKeys($selector, $keys, true, 0);
	}
	
	public function typeSlowly(WebDriverBy|string $selector, string $keys, int $pause = 100): static
	{
		return $this->sendKeys($selector, $keys, true, $pause);
	}
	
	public function append(WebDriverBy|string $selector, string $keys): static
	{
		return $this->sendKeys($selector, $keys, false, 0);
	}
	
	public function appendSlowly(WebDriverBy|string $selector, string $keys, int $pause = 100): static
	{
		return $this->sendKeys($selector, $keys, false, $pause);
	}
	
	/** @return $this|mixed */
	public function value(WebDriverBy|string $selector, $value = null): mixed
	{
		return null === $value
			? $this->getValue($selector)
			: $this->setValue($selector, $value);
	}
	
	public function quit(): static
	{
		return $this->quitBrowser();
	}
	
	public function press(WebDriverBy|string $selector, bool $wait = false): static
	{
		return $this->clickButton($selector, $wait);
	}
	
	public function selected(WebDriverBy|string $selector, string $value): bool
	{
		return $this->getSelected($selector, $value);
	}
	
	public function waitFor(WebDriverBy|string $selector, ?int $seconds = null): static
	{
		return $this->waitUsing(
			seconds: $seconds,
			interval: 100,
			wait: WebDriverExpectedCondition::presenceOfElementLocated(Selector::from($selector)),
			message: 'Did not find selector before timeout.',
		);
	}
	
	public function waitUntilMissing(WebDriverBy|string $selector, ?int $seconds = null): static
	{
		return $this->waitUsing(
			seconds: $seconds,
			interval: 100,
			wait: WebDriverExpectedCondition::not(
				WebDriverExpectedCondition::presenceOfElementLocated(Selector::from($selector))
			),
			message: 'Selector was not removed before timeout.',
		);
	}
	
	public function waitForTextIn(WebDriverBy|string $selector, string $text, ?int $seconds = null): static
	{
		return $this->waitUsing(
			seconds: $seconds,
			interval: 100,
			wait: WebDriverExpectedCondition::elementTextContains(
				by: Selector::from($selector),
				text: $text,
			),
			message: "Did not see text [{$text}] in selector [".Selector::toString($selector).'] before timeout.',
		);
	}
	
	public function waitForText(string $text, ?int $seconds = null): static
	{
		return $this->waitForTextIn('body', $text, $seconds);
	}
	
	public function waitUntilMissingTextIn(WebDriverBy|string $selector, string $text, ?int $seconds = null): static
	{
		return $this->waitUsing(
			seconds: $seconds,
			interval: 100,
			wait: WebDriverExpectedCondition::not(
				WebDriverExpectedCondition::elementTextContains(
					by: Selector::from($selector),
					text: $text,
				)
			),
			message: "Text [{$text}] in selector [".Selector::toString($selector).'] was not removed before timeout.',
		);
	}
	
	public function waitUntilMissingText(string $text, ?int $seconds = null): static
	{
		return $this->waitUntilMissingTextIn('body', $seconds);
	}
	
	public function waitForLink(string $link, ?int $seconds = null): static
	{
		return $this->waitFor(WebDriverBy::linkText($link), $seconds);
	}
	
	public function waitForInput(string $field, ?int $seconds = null): static
	{
		return $this->waitFor("input[name='{$field}'], textarea[name='{$field}'], select[name='{$field}']", $seconds);
	}
	
	public function waitForLocation(string $path, ?int $seconds = null): static
	{
		$message = "Waited for location [{$path}] but timed out.";
		$path = Js::from($path);
		
		return Str::startsWith($path, ['http://', 'https://'])
			? $this->waitUntil('`${location.protocol}//${location.host}${location.pathname}` == '.$path, $seconds, $message)
			: $this->waitUntil("window.location.pathname == {$path}", $seconds, $message);
	}
	
	public function waitForRoute(string $route, $parameters = [], ?int $seconds = null): static
	{
		return $this->waitForLocation(route($route, $parameters, false), $seconds);
	}
	
	public function waitUntil(string $script, ?int $seconds = null, string $message = null): static
	{
		$script = (string) Str::of($script)
			->start('return ')
			->finish(';');
		
		return $this->waitUsing(
			seconds: $seconds,
			interval: 100,
			wait: static function(RemoteWebDriver $driver) use ($script) {
				return $driver->executeScript($script);
			},
			message: $message,
		);
	}
}
