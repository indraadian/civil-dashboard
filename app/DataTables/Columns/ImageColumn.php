<?php

namespace App\DataTables\Columns;

class ImageColumn extends Column
{
    protected string $type = 'image';
    protected ?string $placeholder = null;
    protected string $height = 'h-10';
    protected string $width = 'w-10';

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function defaultImage(string $defaultImage): static
    {
        return $this->placeholder($defaultImage);
    }

    public function size(string $width, string $height): static
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'placeholder' => $this->placeholder,
            'width' => $this->width,
            'height' => $this->height,
        ]);
    }
}
