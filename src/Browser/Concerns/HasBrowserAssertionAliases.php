<?php

namespace Glhd\Dawn\Browser\Concerns;

trait HasBrowserAssertionAliases
{
	public function assertCookieValue(string $name, $value, bool $decrypt = true): static
	{
		return $this->assertHasCookie($name, $value, $decrypt);
	}
	
	public function assertPlainCookieValue(string $name, $value): static
	{
		return $this->assertHasCookie($name, $value, decrypt: false);
	}
	
	public function assertHasPlainCookie(string $name): static
	{
		return $this->assertHasCookie($name, decrypt: false);
	}
	
	public function assertPlainCookieMissing(string $name): static
	{
		return $this->assertCookieMissing($name, decrypt: false);
	}
}