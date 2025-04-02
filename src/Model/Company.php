<?php

declare(strict_types=1);

namespace Nicodemuz\Prh\Model;

use DateMalformedStringException;
use DateTimeImmutable;

final readonly class Company
{
    public function __construct(
        private array $businessId,
        private array $names,
        private ?array $website,
        private array $addresses,
        private ?array $mainBusinessLine
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['businessId'] ?? [],
            $data['names'] ?? [],
            $data['website'] ?? null,
            $data['addresses'] ?? [],
            $data['mainBusinessLine'] ?? null
        );
    }

    public function getBusinessId(): string
    {
        return $this->businessId['value'] ?? '';
    }

    public function getRegistrationDate(): ?DateTimeImmutable
    {
        try {
            return isset($this->businessId['registrationDate'])
                ? new DateTimeImmutable($this->businessId['registrationDate'])
                : null;
        } catch (DateMalformedStringException) {
            return null;
        }
    }

    /**
     * Returns all company names with their metadata.
     *
     * @return array<array{name: string, type: string, registrationDate: string, endDate?: string}>
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * Returns the current company name (latest registered name of type 1 without an end date).
     */
    public function getCurrentName(): ?string
    {
        foreach ($this->names as $name) {
            if ($name['type'] === '1' && !isset($name['endDate'])) {
                return $name['name'];
            }
        }
        return null;
    }

    public function getWebsite(): ?string
    {
        return $this->website['url'] ?? null;
    }

    /**
     * Returns the current street address (type 1 address).
     */
    public function getCurrentStreet(): ?string
    {
        foreach ($this->addresses as $address) {
            if ($address['type'] === 1) {
                return $address['street'] ?? null;
            }
        }
        return null;
    }

    /**
     * Returns the current city for a given language.
     */
    public function getCurrentCity(Language $language = Language::Finnish): ?string
    {
        foreach ($this->addresses as $address) {
            if ($address['type'] === 1 && isset($address['postOffices'])) {
                foreach ($address['postOffices'] as $postOffice) {
                    if ($postOffice['languageCode'] === $language->value) {
                        return $postOffice['city'];
                    }
                }
            }
        }
        return null;
    }

    /**
     * Returns the current postal code (from type 1 address).
     */
    public function getCurrentPostalCode(): ?string
    {
        foreach ($this->addresses as $address) {
            if ($address['type'] === 1) {
                return $address['postCode'] ?? null;
            }
        }
        return null;
    }

    /**
     * Returns the main business line code.
     */
    public function getMainBusinessLineCode(): ?string
    {
        return $this->mainBusinessLine['type'] ?? null;
    }

    /**
     * Returns the main business line description in a given language.
     */
    public function getMainBusinessLineDescription(Language $language = Language::Finnish): ?string
    {
        if (isset($this->mainBusinessLine['descriptions'])) {
            foreach ($this->mainBusinessLine['descriptions'] as $desc) {
                if ($desc['languageCode'] === $language->value) {
                    return $desc['description'];
                }
            }
        }
        return null;
    }
}
