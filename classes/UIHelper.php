<?php
/**
 * UIHelper.php — Reusable UI component renderer
 */
class UIHelper
{
    // ── Heroicons SVG snippets ──────────────────────────────────────────────
    public static function icon(string $name, string $class = 'w-5 h-5'): string
    {
        $icons = [
            'tv'                   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125z"/>',
            'musical-note'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z"/>',
            'cloud'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/>',
            'code-bracket'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>',
            'device-phone-mobile'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18h3"/>',
            'wifi'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/>',
            'bolt'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>',
            'puzzle-piece'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z"/>',
            'newspaper'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/>',
            'heart'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>',
            'briefcase'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/>',
            'ellipsis-horizontal'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>',
            'plus'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
            'pencil'               => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>',
            'trash'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>',
            'eye'                  => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'pause'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5"/>',
            'play'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>',
            'check'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>',
            'x-mark'               => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
            'chevron-left'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>',
            'chevron-right'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>',
            'arrow-download'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>',
            'calendar'             => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
            'chart-bar'            => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
            'bell'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
            'user'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
            'cog'                  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'home'                 => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
            'list-bullet'          => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>',
            'arrow-up-tray'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>',
            'clock'                => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'currency-pound'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.756a4.5 4.5 0 100 8.488M7.5 10.5h5.25m-5.25 3h5.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'arrow-left-on-rect'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>',
            'shield-check'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>',
        ];

        $path = $icons[$name] ?? $icons['ellipsis-horizontal'];
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">%s</svg>',
            htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            $path
        );
    }

    // ── SummaryWidget ──────────────────────────────────────────────────────
    public static function SummaryWidget(
        string $title,
        string $value,
        string $subtitle,
        string $colour,
        string $icon
    ): string {
        $colourMap = [
            'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',   'text' => 'text-blue-600 dark:text-blue-400',   'icon' => 'bg-blue-100 dark:bg-blue-900/40'],
            'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-600 dark:text-indigo-400', 'icon' => 'bg-indigo-100 dark:bg-indigo-900/40'],
            'green'  => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'icon' => 'bg-emerald-100 dark:bg-emerald-900/40'],
            'amber'  => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400',  'icon' => 'bg-amber-100 dark:bg-amber-900/40'],
        ];
        $c = $colourMap[$colour] ?? $colourMap['blue'];

        return <<<HTML
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">{$title}</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white truncate">{$value}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 truncate">{$subtitle}</p>
                </div>
                <div class="flex-shrink-0 ml-4">
                    <div class="w-12 h-12 {$c['icon']} rounded-xl flex items-center justify-center">
                        <span class="{$c['text']}">{$icon}</span>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    // ── Badge ──────────────────────────────────────────────────────────────
    public static function Badge(string $label, string $colour = '#6366F1', string $extra = ''): string
    {
        $hex = ltrim($colour, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return sprintf(
            '<span %s style="background-color:rgba(%d,%d,%d,0.12);color:%s" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium">%s</span>',
            $extra,
            $r, $g, $b,
            htmlspecialchars($colour, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    // ── Status Badge ───────────────────────────────────────────────────────
    public static function StatusBadge(string $status): string
    {
        $map = [
            'active'    => ['text' => 'Active',    'cls' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'],
            'paused'    => ['text' => 'Paused',    'cls' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400'],
            'cancelled' => ['text' => 'Cancelled', 'cls' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'],
        ];
        $s = $map[$status] ?? $map['active'];
        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold %s">%s</span>',
            $s['cls'],
            htmlspecialchars($s['text'], ENT_QUOTES, 'UTF-8')
        );
    }

    // ── Alert ──────────────────────────────────────────────────────────────
    public static function Alert(string $type, string $message): string
    {
        $map = [
            'success' => ['cls' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300', 'icon' => 'check'],
            'error'   => ['cls' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300', 'icon' => 'x-mark'],
            'warning' => ['cls' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300', 'icon' => 'bolt'],
            'info'    => ['cls' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300', 'icon' => 'bell'],
        ];
        $a = $map[$type] ?? $map['info'];
        $msg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        return <<<HTML
        <div class="flex items-start gap-3 p-4 rounded-xl border {$a['cls']} mb-4" role="alert">
            <span class="flex-shrink-0 mt-0.5">{$a['icon']}</span>
            <p class="text-sm font-medium">{$msg}</p>
        </div>
        HTML;
    }

    // ── Modal ──────────────────────────────────────────────────────────────
    public static function Modal(string $id, string $title, string $body, string $footer = ''): string
    {
        $safeId    = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $footerHtml = $footer ?: '<button type="button" onclick="closeModal(\'' . $safeId . '\')" class="btn-secondary">Close</button>';
        return <<<HTML
        <div id="{$safeId}" class="modal-backdrop hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="{$safeId}-title">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('{$safeId}')"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 id="{$safeId}-title" class="text-lg font-semibold text-slate-900 dark:text-white">{$safeTitle}</h2>
                    <button onclick="closeModal('{$safeId}')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-4">{$body}</div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">{$footerHtml}</div>
            </div>
        </div>
        HTML;
    }

    // ── Pagination ─────────────────────────────────────────────────────────
    public static function Pagination(int $total, int $page, int $perPage, string $baseUrl): string
    {
        $totalPages = (int)ceil($total / $perPage);
        if ($totalPages <= 1) return '';

        $html = '<nav class="flex items-center justify-center gap-1 mt-8" aria-label="Pagination">';

        // Previous
        if ($page > 1) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($page - 1) . '" class="pagination-btn">' . self::icon('chevron-left', 'w-4 h-4') . '</a>';
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === $page) {
                $html .= '<span class="pagination-btn pagination-active">' . $i . '</span>';
            } elseif ($i === 1 || $i === $totalPages || abs($i - $page) <= 2) {
                $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-btn">' . $i . '</a>';
            } elseif (abs($i - $page) === 3) {
                $html .= '<span class="pagination-btn opacity-50">…</span>';
            }
        }

        // Next
        if ($page < $totalPages) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($page + 1) . '" class="pagination-btn">' . self::icon('chevron-right', 'w-4 h-4') . '</a>';
        }

        $html .= '</nav>';
        return $html;
    }

    // ── Subscription Card ──────────────────────────────────────────────────
    public static function SubscriptionCard(array $sub, array $category): string
    {
        $name       = htmlspecialchars($sub['name'], ENT_QUOTES, 'UTF-8');
        $provider   = htmlspecialchars($sub['provider'] ?? '', ENT_QUOTES, 'UTF-8');
        $amount     = CurrencyHelper::format((float)$sub['amount'], $sub['currency'] ?? 'GBP');
        $cycleLabel = DateHelper::billingCycleLabel($sub['billing_cycle'] ?? 'monthly');
        $nextDate   = DateHelper::formatUK($sub['next_billing_date']);
        $dueLabel   = DateHelper::dueLabel($sub['next_billing_date']);
        $daysUntil  = DateHelper::daysUntil($sub['next_billing_date']);
        $statusBadge = self::StatusBadge($sub['status'] ?? 'active');
        $catBadge   = self::Badge(htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'), $category['colour'] ?? '#6366F1');
        $editUrl    = UrlHelper::base("dashboard/edit-subscription.php?id={$sub['id']}");
        $viewUrl    = UrlHelper::base("dashboard/view-subscription.php?id={$sub['id']}");
        $isPaused   = ($sub['status'] ?? '') === 'paused';
        $isActive   = ($sub['status'] ?? '') === 'active';

        // Logo
        $logoHtml = self::SubscriptionLogo($sub, 40);

        // Urgency highlight
        $urgency = '';
        if ($daysUntil >= 0 && $daysUntil <= 3 && ($sub['status'] ?? '') === 'active') {
            $urgency = 'ring-2 ring-amber-400 dark:ring-amber-500';
        }

        $pauseBtn = '';
        if ($isActive) {
            $pauseBtn = '<button onclick="pauseSubscription(' . $sub['id'] . ')" class="btn-icon text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20" title="Pause">' . self::icon('pause', 'w-4 h-4') . '</button>';
        } elseif ($isPaused) {
            $pauseBtn = '<button onclick="resumeSubscription(' . $sub['id'] . ')" class="btn-icon text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20" title="Resume">' . self::icon('play', 'w-4 h-4') . '</button>';
        }

        return <<<HTML
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 p-5 hover:shadow-md transition-all duration-200 {$urgency}" data-sub-id="{$sub['id']}">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">{$logoHtml}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white text-base leading-snug">{$name}</h3>
                            {$provider ? '<p class="text-sm text-slate-500 dark:text-slate-400">' . $provider . '</p>' : ''}
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">{$statusBadge}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        {$catBadge}
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{$amount}</span>
                        <span class="text-sm text-slate-400 dark:text-slate-500">/ {$cycleLabel}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            <span class="font-medium text-slate-700 dark:text-slate-300">{$nextDate}</span>
                            &nbsp;·&nbsp;
                            <span class="text-amber-600 dark:text-amber-400">{$dueLabel}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{$viewUrl}" class="btn-icon text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="View">
                                {$this::icon('eye', 'w-4 h-4')}
                            </a>
                            <a href="{$editUrl}" class="btn-icon text-slate-400 hover:text-blue-500" title="Edit">
                                {$this::icon('pencil', 'w-4 h-4')}
                            </a>
                            {$pauseBtn}
                            <button onclick="confirmDelete({$sub['id']}, '{$name}')" class="btn-icon text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
                                {$this::icon('trash', 'w-4 h-4')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    // ── Subscription Logo ──────────────────────────────────────────────────
    public static function SubscriptionLogo(array $sub, int $size = 40): string
    {
        $sizeClass = "w-{$size} h-{$size}";
        $sizePx = $size * 4; // Tailwind default: 1 unit = 4px

        if (!empty($sub['logo_path']) && file_exists(dirname(__DIR__) . '/assets/images/logos/' . $sub['logo_path'])) {
            $src = UrlHelper::asset('images/logos/' . $sub['logo_path']);
            return "<img src=\"{$src}\" alt=\"\" class=\"w-10 h-10 rounded-lg object-contain bg-white border border-slate-100 dark:border-slate-700 p-1\">";
        }

        if (!empty($sub['logo_url'])) {
            $src = htmlspecialchars($sub['logo_url'], ENT_QUOTES, 'UTF-8');
            return "<img src=\"{$src}\" alt=\"\" class=\"w-10 h-10 rounded-lg object-contain bg-white border border-slate-100 dark:border-slate-700 p-1\" onerror=\"this.replaceWith(document.getElementById('logo-fallback-{$sub['id']}'))\">";
        }

        // Favicon fallback
        if (!empty($sub['url'])) {
            $domain = parse_url($sub['url'], PHP_URL_HOST);
            if ($domain) {
                $favicon = "https://www.google.com/s2/favicons?domain={$domain}&sz=64";
                return "<img src=\"{$favicon}\" alt=\"\" class=\"w-10 h-10 rounded-lg object-contain bg-white border border-slate-100 dark:border-slate-700 p-1\" onerror=\"this.replaceWith(document.getElementById('logo-fallback-{$sub['id']}'))\">";
            }
        }

        // Text avatar fallback
        return self::LogoAvatar($sub['name'], $sub['id'] ?? 0);
    }

    public static function LogoAvatar(string $name, int $id): string
    {
        $initials = strtoupper(substr(str_word_count($name, 1)[0] ?? $name, 0, 1));
        $colours = ['#EF4444','#8B5CF6','#3B82F6','#10B981','#F59E0B','#6366F1','#EC4899','#14B8A6'];
        $bg = $colours[$id % count($colours)];
        return <<<HTML
        <div id="logo-fallback-{$id}" class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm flex-shrink-0" style="background-color:{$bg}">
            {$initials}
        </div>
        HTML;
    }

    // ── Calendar Grid ──────────────────────────────────────────────────────
    public static function CalendarGrid(array $eventsByDay, int $year, int $month): string
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDow    = (int)(new DateTime("{$year}-{$month}-01"))->format('N'); // 1=Mon, 7=Sun
        $today       = date('Y-m-d');
        $monthNames  = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
        $monthName   = $monthNames[$month];

        $prevMonth = $month - 1; $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1; $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        $html  = '<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">';
        // Header
        $html .= '<div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">';
        $html .= '<button class="btn-icon" hx-get="' . UrlHelper::base("ajax/calendar_month.php?year={$prevYear}&month={$prevMonth}") . '" hx-target="#calendar-grid" onclick="loadCalendarMonth(' . $prevYear . ',' . $prevMonth . ')" aria-label="Previous month">' . self::icon('chevron-left', 'w-5 h-5') . '</button>';
        $html .= '<h2 class="text-lg font-semibold text-slate-900 dark:text-white" id="calendar-title">' . $monthName . ' ' . $year . '</h2>';
        $html .= '<button class="btn-icon" onclick="loadCalendarMonth(' . $nextYear . ',' . $nextMonth . ')" aria-label="Next month">' . self::icon('chevron-right', 'w-5 h-5') . '</button>';
        $html .= '</div>';

        // Day headers
        $html .= '<div class="grid grid-cols-7 text-center text-xs font-semibold text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-700">';
        foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d) {
            $html .= '<div class="py-2">' . $d . '</div>';
        }
        $html .= '</div>';

        // Grid
        $html .= '<div class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-700">';
        // Leading empty cells
        for ($e = 1; $e < $firstDow; $e++) {
            $html .= '<div class="min-h-[80px] p-1 bg-slate-50 dark:bg-slate-900/30"></div>';
        }
        // Days
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $isToday = $dateKey === $today;
            $isPast  = $dateKey < $today;
            $events  = $eventsByDay[$dateKey] ?? [];

            $dayClass = 'min-h-[80px] p-1.5 relative';
            if ($isToday)    $dayClass .= ' bg-blue-50 dark:bg-blue-900/10';
            elseif ($isPast) $dayClass .= ' bg-slate-50/60 dark:bg-slate-900/20 opacity-60';

            $html .= '<div class="' . $dayClass . '">';
            $numClass = 'text-xs font-semibold w-6 h-6 flex items-center justify-center rounded-full mb-1 ';
            $numClass .= $isToday ? 'bg-blue-600 text-white' : 'text-slate-500 dark:text-slate-400';
            $html .= '<span class="' . $numClass . '">' . $d . '</span>';

            foreach (array_slice($events, 0, 3) as $ev) {
                $label  = htmlspecialchars($ev['name'], ENT_QUOTES, 'UTF-8');
                $amt    = CurrencyHelper::format((float)$ev['amount'], $ev['currency'] ?? 'GBP');
                $colour = $ev['category_colour'] ?? '#6366F1';
                $html .= '<div class="calendar-chip mb-0.5 cursor-pointer" style="background-color:rgba(' . implode(',', sscanf(ltrim($colour,'#'), '%02x%02x%02x')) . ',0.15);color:' . $colour . '" onclick="showCalendarModal(\'' . $dateKey . '\')" title="' . $label . ' — ' . $amt . '">';
                $html .= '<span class="calendar-chip-text">' . $label . '</span>';
                $html .= '<span class="calendar-chip-amount">' . $amt . '</span>';
                $html .= '</div>';
            }
            if (count($events) > 3) {
                $extra = count($events) - 3;
                $html .= '<button class="text-xs text-blue-600 dark:text-blue-400 font-medium" onclick="showCalendarModal(\'' . $dateKey . '\')">+' . $extra . ' more</button>';
            }

            $html .= '</div>';
        }
        // Trailing empty cells
        $total = $firstDow - 1 + $daysInMonth;
        $trailing = (7 - ($total % 7)) % 7;
        for ($t = 0; $t < $trailing; $t++) {
            $html .= '<div class="min-h-[80px] p-1 bg-slate-50 dark:bg-slate-900/30"></div>';
        }

        $html .= '</div></div>';
        return $html;
    }

    // ── Google Sign-In Button ──────────────────────────────────────────────
    public static function GoogleSignInButton(string $label = 'Continue with Google'): string
    {
        $url = UrlHelper::base('auth/google.php');
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        return <<<HTML
        <a href="{$url}" class="flex items-center justify-center gap-3 w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all duration-200 shadow-sm hover:shadow group">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            <span>{$label}</span>
        </a>
        HTML;
    }

    // ── Empty State ──────────────────────────────────────────────────────────
    public static function EmptyState(string $title, string $subtitle, string $actionUrl = '', string $actionLabel = ''): string
    {
        $svg = '<svg class="w-24 h-24 mx-auto text-slate-200 dark:text-slate-700 mb-6" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="8" y="24" width="80" height="56" rx="8" fill="currentColor" opacity=".5"/><rect x="20" y="16" width="56" height="64" rx="6" fill="currentColor"/><path d="M36 44h24M36 54h16" stroke="white" stroke-width="3" stroke-linecap="round"/><circle cx="72" cy="72" r="12" fill="#6366F1"/><path d="M76 72h-8M72 68v8" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>';

        $actionHtml = '';
        if ($actionUrl && $actionLabel) {
            $actionHtml = '<a href="' . htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8') . '" class="btn-primary mt-4 inline-flex items-center gap-2">' . self::icon('plus', 'w-4 h-4') . htmlspecialchars($actionLabel, ENT_QUOTES, 'UTF-8') . '</a>';
        }

        return <<<HTML
        <div class="flex flex-col items-center justify-center py-16 text-center">
            {$svg}
            <h3 class="text-xl font-semibold text-slate-700 dark:text-slate-300 mb-2">{$title}</h3>
            <p class="text-slate-500 dark:text-slate-400 max-w-sm">{$subtitle}</p>
            {$actionHtml}
        </div>
        HTML;
    }
}
