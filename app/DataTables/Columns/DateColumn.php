<?php

namespace App\DataTables\Columns;

class DateColumn extends Column
{
    protected string $type = 'date';
    protected string $format = 'd F Y';
    protected string $locale = 'id-ID';

    /**
     * Set the date format.
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Set the locale for date formatting.
     */
    public function locale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format,
            'locale' => $this->locale,
        ]);
    }
}
