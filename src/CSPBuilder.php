<?php

declare(strict_types=1);

namespace EasyShield\SecureHeaders;

/**
 * CSPBuilder - A fluent API for building Content Security Policy
 */
class CSPBuilder
{
    /** @var array CSP directives */
    private array $directives = [];
    
    /** @var SecureHeaders Main SecureHeaders instance */
    private SecureHeaders $secureHeaders;
    
    /** @var bool Whether to use nonce */
    private bool $useNonce = true;
    
    /** @var array Store script content hashes */
    private array $scriptHashes = [];
    
    /** @var array Store style content hashes */
    private array $styleHashes = [];

    /**
     * Create a new CSPBuilder instance
     */
    public function __construct(SecureHeaders $secureHeaders)
    {
        $this->secureHeaders = $secureHeaders;
        // Set default directives
        $this->directives = [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
        ];
    }

    /**
     * Allow scripts from specified sources
     */
    public function allowScripts(string ...$sources): self
    {
        $scriptSources = empty($sources) ? ["'self'"] : $sources;
        
        if ($this->useNonce) {
            $nonce = $this->secureHeaders->generateNonce();
            $scriptSources[] = "'nonce-{$nonce}'";
        }
        
        if (!empty($this->scriptHashes)) {
            $scriptSources = array_merge($scriptSources, $this->scriptHashes);
        }
        
        $this->directives['script-src'] = array_unique(
            array_merge(
                $this->directives['script-src'] ?? [],
                $scriptSources
            )
        );
        
        return $this;
    }

    /**
     * Allow styles from specified sources
     */
    public function allowStyles(string ...$sources): self
    {
        $styleSources = empty($sources) ? ["'self'"] : $sources;
        
        if ($this->useNonce) {
            $nonce = $this->secureHeaders->generateNonce();
            $styleSources[] = "'nonce-{$nonce}'";
        }
        
        if (!empty($this->styleHashes)) {
            $styleSources = array_merge($styleSources, $this->styleHashes);
        }
        
        $this->directives['style-src'] = array_unique(
            array_merge(
                $this->directives['style-src'] ?? [],
                $styleSources
            )
        );
        
        return $this;
    }

    /**
     * Allow images from specified sources
     */
    public function allowImages(string ...$sources): self
    {
        $imageSources = empty($sources) ? ["'self'", "data:"] : $sources;
        
        $this->directives['img-src'] = array_unique(
            array_merge(
                $this->directives['img-src'] ?? [],
                $imageSources
            )
        );
        
        return $this;
    }

    /**
     * Allow fonts from specified sources
     */
    public function allowFonts(string ...$sources): self
    {
        $fontSources = empty($sources) ? ["'self'"] : $sources;
        
        $this->directives['font-src'] = array_unique(
            array_merge(
                $this->directives['font-src'] ?? [],
                $fontSources
            )
        );
        
        return $this;
    }

    /**
     * Allow connections to specified sources
     */
    public function allowConnections(string ...$sources): self
    {
        $connectionSources = empty($sources) ? ["'self'"] : $sources;
        
        $this->directives['connect-src'] = array_unique(
            array_merge(
                $this->directives['connect-src'] ?? [],
                $connectionSources
            )
        );
        
        return $this;
    }

    /**
     * Set frame-ancestors directive
     */
    public function allowFrameAncestors(string ...$sources): self
    {
        $frameAncestors = empty($sources) ? ["'self'"] : $sources;
        
        $this->directives['frame-ancestors'] = $frameAncestors;
        
        return $this;
    }

    /**
     * Block frames (set frame-ancestors to 'none')
     */
    public function blockFrames(): self
    {
        $this->directives['frame-ancestors'] = ["'none'"];
        return $this;
    }

    /**
     * Allow unsafe-inline for scripts (not recommended)
     */
    public function allowUnsafeInlineScripts(): self
    {
        if (!isset($this->directives['script-src'])) {
            $this->allowScripts();
        }
        
        $this->directives['script-src'][] = "'unsafe-inline'";
        $this->directives['script-src'] = array_unique($this->directives['script-src']);
        
        return $this;
    }

    /**
     * Allow unsafe-inline for styles
     */
    public function allowUnsafeInlineStyles(): self
    {
        if (!isset($this->directives['style-src'])) {
            $this->allowStyles();
        }
        
        $this->directives['style-src'][] = "'unsafe-inline'";
        $this->directives['style-src'] = array_unique($this->directives['style-src']);
        
        return $this;
    }

    /**
     * Allow unsafe-eval for scripts (not recommended)
     */
    public function allowUnsafeEval(): self
    {
        if (!isset($this->directives['script-src'])) {
            $this->allowScripts();
        }
        
        $this->directives['script-src'][] = "'unsafe-eval'";
        $this->directives['script-src'] = array_unique($this->directives['script-src']);
        
        return $this;
    }

    /**
     * Enable upgrade-insecure-requests directive
     */
    public function upgradeInsecureRequests(): self
    {
        $this->directives['upgrade-insecure-requests'] = [];
        return $this;
    }

    /**
     * Enable strict-dynamic for scripts
     */
    public function useStrictDynamic(): self
    {
        if (!isset($this->directives['script-src'])) {
            $this->allowScripts();
        }
        
        $this->directives['script-src'][] = "'strict-dynamic'";
        $this->directives['script-src'] = array_unique($this->directives['script-src']);
        
        return $this;
    }

    /**
     * Disable nonce generation
     */
    public function withoutNonce(): self
    {
        $this->useNonce = false;
        return $this;
    }

    /**
     * Add hash for inline script
     */
    public function addScriptHash(string $algorithm, string $hash): self
    {
        $this->scriptHashes[] = "'{$algorithm}-{$hash}'";
        
        if (!isset($this->directives['script-src'])) {
            $this->allowScripts();
        } else {
            $this->directives['script-src'] = array_unique(
                array_merge(
                    $this->directives['script-src'],
                    [$this->scriptHashes[count($this->scriptHashes) - 1]]
                )
            );
        }
        
        return $this;
    }

    /**
     * Add hash for inline style
     */
    public function addStyleHash(string $algorithm, string $hash): self
    {
        $this->styleHashes[] = "'{$algorithm}-{$hash}'";
        
        if (!isset($this->directives['style-src'])) {
            $this->allowStyles();
        } else {
            $this->directives['style-src'] = array_unique(
                array_merge(
                    $this->directives['style-src'],
                    [$this->styleHashes[count($this->styleHashes) - 1]]
                )
            );
        }
        
        return $this;
    }

    /**
     * Set the default-src directive
     */
    public function setDefaultSrc(string ...$sources): self
    {
        $this->directives['default-src'] = empty($sources) ? ["'self'"] : $sources;
        return $this;
    }

    /**
     * Get the built CSP directives
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * Detect external resources from HTML and add them to appropriate CSP directives
     */
    public function detectExternalResourcesFromHtml(string $html): self
    {
        // Detect and add script sources
        if (preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                if ($this->isExternalUrl($src)) {
                    $this->extractAndAddSource('script-src', $src);
                }
            }
        }
        
        // Detect and add style sources
        if (preg_match_all('/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $href) {
                if ($this->isExternalUrl($href)) {
                    $this->extractAndAddSource('style-src', $href);
                }
            }
        }
        
        // Detect and add style sources from @import
        if (preg_match_all('/@import\s+(?:url\()?[\'"]([^\'"()]+)[\'"](?:\))?/i', $html, $matches)) {
            foreach ($matches[1] as $href) {
                if ($this->isExternalUrl($href)) {
                    $this->extractAndAddSource('style-src', $href);
                }
            }
        }
        
        // Detect and add image sources
        if (preg_match_all('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                if ($this->isExternalUrl($src)) {
                    $this->extractAndAddSource('img-src', $src);
                }
            }
        }
        
        // Detect and add font sources
        if (preg_match_all('/@font-face\s*{[^}]*src:[^;]*url\(["\']?([^"\'()]+)["\']?\)/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                if ($this->isExternalUrl($src)) {
                    $this->extractAndAddSource('font-src', $src);
                }
            }
        }
        
        // Detect and add frame sources
        if (preg_match_all('/<iframe[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                if ($this->isExternalUrl($src)) {
                    $this->extractAndAddSource('frame-src', $src);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Inject nonces into script and style tags in HTML
     */
    public function injectNoncesToHtml(string $html): string
    {
        if (!$this->useNonce) {
            return $html;
        }
        
        $nonce = $this->secureHeaders->generateNonce();
        
        // Inject nonce into script tags that don't have nonce attribute
        $html = preg_replace_callback(
            '/<script([^>]*)>/i',
            function ($matches) use ($nonce) {
                if (strpos($matches[1], 'nonce=') === false) {
                    return "<script{$matches[1]} nonce=\"{$nonce}\">";
                }
                return $matches[0];
            },
            $html
        );
        
        // Inject nonce into style tags that don't have nonce attribute
        $html = preg_replace_callback(
            '/<style([^>]*)>/i',
            function ($matches) use ($nonce) {
                if (strpos($matches[1], 'nonce=') === false) {
                    return "<style{$matches[1]} nonce=\"{$nonce}\">";
                }
                return $matches[0];
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Check if URL is external (not relative, data, or javascript)
     */
    private function isExternalUrl(string $url): bool
    {
        return !empty($url) && 
               strpos($url, 'data:') !== 0 && 
               strpos($url, 'javascript:') !== 0 && 
               strpos($url, '//') === 0 || 
               preg_match('/^https?:\/\//i', $url);
    }
    
    /**
     * Extract domain from URL and add to appropriate CSP directive
     */
    private function extractAndAddSource(string $directive, string $url): void
    {
        // Convert protocol-relative URLs to https
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }
        
        $parts = parse_url($url);
        if ($parts && isset($parts['host'])) {
            $source = $parts['scheme'] . '://' . $parts['host'];
            if (isset($parts['port'])) {
                $source .= ':' . $parts['port'];
            }
            
            if (!isset($this->directives[$directive])) {
                $this->directives[$directive] = ["'self'"];
            }
            
            $this->directives[$directive][] = $source;
            $this->directives[$directive] = array_unique($this->directives[$directive]);
        }
    }
} 