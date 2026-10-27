@extends('layouts.base')

@section('title', 'Blade Architectural Showcase')

@section('styles')
<style>
    .hero {
        margin-bottom: 2.5rem;
        text-align: center;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: #A5B4FC;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    .hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.2;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #FFFFFF 30%, #9CA3AF 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .grid-3 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .section-title {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .product-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-radius: 14px;
        background: rgba(17, 24, 39, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.06);
        padding: 1.25rem;
        transition: all 0.2s ease;
    }
    .product-card:hover {
        border-color: rgba(99, 102, 241, 0.4);
        background: rgba(17, 24, 39, 0.85);
        transform: translateY(-2px);
    }
    .badge {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-indigo { background: rgba(99, 102, 241, 0.2); color: #A5B4FC; }
    .badge-emerald { background: rgba(16, 185, 129, 0.2); color: #6EE7B7; }
    .table-responsive { overflow-x: auto; margin-top: 1rem; }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
    th { padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); color: #9CA3AF; font-weight: 600; }
    td { padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.04); }
    .code-pill {
        background: rgba(255, 255, 255, 0.06);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #E0E7FF;
    }
</style>
@endsection

@section('content')
<div class="hero">
    <div class="hero-badge">⚡ Phase 5 Core Concept</div>
    <h1>Blade Templating & Decoupled Frontend Integration</h1>
    <p style="color: var(--text-muted); font-size: 1.05rem;">
        Mastering Laravel's native templating engine alongside our decoupled React 18 + Vite Single Page Application.
    </p>
</div>

<!-- Comparison & Concept Showcase -->
<div class="grid-2">
    <div class="glass-card">
        <h2 class="section-title">
            <span style="color: #818CF8;">❖</span> Blade Engine vs Django Templates (DTL)
        </h2>
        <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.95rem;">
            Unlike Django DTL which restricts arbitrary Python execution inside templates, Blade compiles into raw, cached PHP:
        </p>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Concept</th>
                        <th>Django (DTL)</th>
                        <th>Laravel (Blade)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Layout Inheritance</td>
                        <td><span class="code-pill">{% extends 'base.html' %}</span></td>
                        <td><span class="code-pill">@extends('layouts.base')</span></td>
                    </tr>
                    <tr>
                        <td>Section Insertion</td>
                        <td><span class="code-pill">{% block content %}</span></td>
                        <td><span class="code-pill">@section('content') / @yield</span></td>
                    </tr>
                    <tr>
                        <td>Escaped Output</td>
                        <td><span class="code-pill">{{ var }}</span></td>
                        <td><span class="code-pill">@{{ $var }}</span></td>
                    </tr>
                    <tr>
                        <td>Raw / Unescaped</td>
                        <td><span class="code-pill">{{ var|safe }}</span></td>
                        <td><span class="code-pill">{!! $var !!}</span></td>
                    </tr>
                    <tr>
                        <td>Auth State Check</td>
                        <td><span class="code-pill">{% if request.user.is_authenticated %}</span></td>
                        <td><span class="code-pill">@auth / @guest</span></td>
                    </tr>
                    <tr>
                        <td>Loops & Empty States</td>
                        <td><span class="code-pill">{% for x in list %} {% empty %}</span></td>
                        <td><span class="code-pill">@forelse($list as $x) @empty</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card">
        <h2 class="section-title">
            <span style="color: #34D399;">🛡️</span> Security & Escaping Demonstration
        </h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1rem;">
            Blade protects against XSS by default via <span class="code-pill">htmlspecialchars()</span> during compilation:
        </p>

        <div style="background: rgba(0, 0, 0, 0.4); border-radius: 8px; padding: 1rem; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.06);">
            <div style="font-size: 0.8rem; color: #9CA3AF; margin-bottom: 0.35rem;">Raw String In Variable:</div>
            <code style="color: #F87171;">&lt;b style="color: #34D399;"&gt;Safe Clean HTML Highlight&lt;/b&gt;</code>
        </div>

        <div style="margin-bottom: 1rem;">
            <div style="font-size: 0.85rem; font-weight: 600; color: #A5B4FC; margin-bottom: 0.25rem;">1. Escaped using &#123;&#123; $highlight &#125;&#125; (Default XSS Safe):</div>
            <div style="background: rgba(255,255,255,0.04); padding: 0.5rem 0.75rem; border-radius: 6px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                {{ $sample_html ?? '<b style="color: #34D399;">Safe Clean HTML Highlight</b>' }}
            </div>
        </div>

        <div>
            <div style="font-size: 0.85rem; font-weight: 600; color: #34D399; margin-bottom: 0.25rem;">2. Rendered using &#123;!! $highlight !!&#125; (Raw HTML):</div>
            <div style="background: rgba(255,255,255,0.04); padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.9rem;">
                {!! $sample_html ?? '<b style="color: #34D399;">Safe Clean HTML Highlight</b>' !!}
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Blade Directives Loop Demonstration -->
<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 class="section-title" style="margin-bottom: 0.25rem;">
                <span style="color: #F59E0B;">📦</span> Active Catalog Demonstration (Blade @forelse loop)
            </h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Rendering Eloquent models server-side with relationship eager-loading and badge states.
            </p>
        </div>
        <a href="http://localhost:5173" target="_blank" style="background: linear-gradient(135deg, #6366F1, #8B5CF6); color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;">
            <span>Open React 18 SPA</span> ➔
        </a>
    </div>

    <div class="grid-3" style="margin-bottom: 0;">
        @forelse($products as $product)
            <div class="product-card">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <span class="badge badge-indigo">{{ $product->category->name ?? 'Uncategorized' }}</span>
                        <span class="badge badge-emerald">SKU: {{ $product->sku }}</span>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.5rem; color: #FFFFFF;">
                        {{ $product->name }}
                    </h3>
                    <p style="color: #9CA3AF; font-size: 0.85rem; margin-bottom: 1rem; line-height: 1.4;">
                        {{ \Illuminate\Support\Str::limit($product->description, 80) }}
                    </p>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 0.75rem; margin-top: 0.5rem;">
                    <div>
                        <span style="font-size: 1.15rem; font-weight: 800; color: #F3F4F6;">
                            ${{ number_format($product->price, 2) }}
                        </span>
                        @if($product->sale_price)
                            <span style="font-size: 0.8rem; color: #EF4444; text-decoration: line-through; margin-left: 0.25rem;">
                                ${{ number_format($product->sale_price, 2) }}
                            </span>
                        @endif
                    </div>
                    <span style="font-size: 0.8rem; color: {{ $product->stock_quantity > 0 ? '#34D399' : '#EF4444' }};">
                        @if($product->stock_quantity > 0)
                            ● {{ $product->stock_quantity }} in stock
                        @else
                            ✕ Out of stock
                        @endif
                    </span>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #9CA3AF;">
                No products found in the catalog. Please run database seeders.
            </div>
        @endforelse
    </div>
</div>
@endsection
