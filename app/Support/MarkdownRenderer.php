<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class MarkdownRenderer
{
    /** @var array<int, string> */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'del',
        'ul', 'ol', 'li', 'blockquote', 'code', 'pre',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'a', 'hr', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /** @var array<int, string> */
    private const BLOCKED_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
        'button', 'textarea', 'select', 'option', 'link', 'meta', 'base',
        'svg', 'math', 'video', 'audio', 'source', 'track', 'canvas',
    ];

    public function render(?string $markdown): string
    {
        $html = str((string) $markdown)->markdown()->toString();

        if (trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML(
            '<div id="markdown-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        $root = $document->getElementById('markdown-root');

        if (! $root instanceof DOMElement) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return '';
        }

        $this->sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $output;
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        $children = iterator_to_array($parent->childNodes);

        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::BLOCKED_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->unwrap($child);

                continue;
            }

            $this->sanitizeAttributes($child, $tag);
            $this->sanitizeChildren($child);
        }
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $attributes = iterator_to_array($element->attributes);

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->name);

            $allowed = match ($tag) {
                'a' => in_array($name, ['href', 'title'], true),
                'img' => in_array($name, ['src', 'alt', 'title', 'width', 'height'], true),
                'th', 'td' => in_array($name, ['colspan', 'rowspan'], true),
                default => false,
            };

            if (! $allowed) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! $this->isSafeUrl($attribute->value, $tag === 'img')) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('rel', 'nofollow noopener noreferrer');
        }
    }

    private function isSafeUrl(string $url, bool $image = false): bool
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return ! $image;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($image) {
            return in_array($scheme, ['http', 'https'], true);
        }

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }
}
