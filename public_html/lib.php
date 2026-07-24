<?php
declare(strict_types=1);

const APP_NAME = 'プロ厨房HIT沖縄';
// ローカルではプロジェクト直下、サブドメイン公開時は public_html の外へ保存する。
define('DATA_DIR', basename(dirname(__DIR__)) === 'public_html'
    ? dirname(__DIR__, 2) . '/storage-demo'
    : __DIR__ . '/../storage');
const UPLOAD_DIR = __DIR__ . '/uploads';
const UPLOAD_IMAGE_MAX_EDGE = 1920;

function boot_app(): void
{
    $sessionDirectory = DATA_DIR . '/sessions';
    if (!is_dir($sessionDirectory)) {
        mkdir($sessionDirectory, 0775, true);
    }
    session_save_path($sessionDirectory);
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => isset($_SERVER['HTTPS'])]);
        session_start();
    }
    foreach ([DATA_DIR, DATA_DIR . '/content', UPLOAD_DIR] as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
    seed_content();
}

function seed_content(): void
{
    $defaults = [
        'hero' => [
            ['id' => 'hero-1', 'title' => '沖縄を、飲食店から元気にする。', 'lead' => '構想から開業、その先まで。飲食店づくりを一社で支えます。', 'color' => '#777b7d', 'image' => '', 'overlay' => '#102a43', 'overlay_opacity' => 38, 'dots' => true, 'dots_opacity' => 18, 'published' => true],
            ['id' => 'hero-2', 'title' => '機械ではなく、繁盛するお店を考える。', 'lead' => '経営と現場、両方の経験から厨房と店舗をご提案します。', 'color' => '#e9e2d3', 'image' => '', 'overlay' => '#1d3557', 'overlay_opacity' => 24, 'dots' => true, 'dots_opacity' => 14, 'published' => true],
            ['id' => 'hero-3', 'title' => '開業から、設備更新、店舗売却まで。', 'lead' => '飲食店のライフサイクルに、長く寄り添うパートナーです。', 'color' => '#14283d', 'image' => '', 'overlay' => '#07182a', 'overlay_opacity' => 48, 'dots' => true, 'dots_opacity' => 18, 'published' => true],
        ],
        'works' => [
            ['id' => 'work-1', 'title' => '那覇市・飲食店 厨房導入', 'category' => '新規開業', 'area' => '那覇市', 'summary' => '動線と将来の設備更新まで見据えた厨房計画。', 'latitude' => 26.2124, 'longitude' => 127.6809, 'image' => '', 'published' => true],
            ['id' => 'work-2', 'title' => '沖縄市・店舗改装', 'category' => '内外装工事', 'area' => '沖縄市', 'summary' => '既存設備を活かしながら店舗全体をリニューアル。', 'latitude' => 26.3344, 'longitude' => 127.8056, 'image' => '', 'published' => true],
        ],
        'maps' => [],
        'news' => [
            ['id' => 'news-1', 'title' => 'プロ厨房HIT沖縄 Webサイト準備中', 'category' => 'お知らせ', 'body' => '現在、Webサイトの公開準備を進めています。', 'published_at' => date('Y-m-d'), 'published' => true],
        ],
        'company' => [[
            'id' => 'company-profile', 'logo' => '',
            'company_name' => '株式会社 プロ厨房HIT 沖縄', 'company_name_en' => 'PRO CHUBO HIT OKINAWA',
            'postal_code' => '', 'address' => '沖縄県', 'phone' => '', 'email' => '',
            'hours' => '', 'closed_days' => '', 'representative' => '新垣 大作', 'executive' => '高見 昌也',
            'affiliation' => 'プロ厨房HIT フランチャイズ加盟店',
            'description' => '飲食店の開業準備から店舗づくり、設備導入、工事、開業後、店舗売却までトータルでサポートします。',
            'history' => "プロ厨房HIT沖縄 設立|飲食店づくりのトータルサポートを開始\nウリテン沖縄 展開|店舗の居抜き売買支援を開始",
        ]],
        'services' => [[
            'id' => 'kitchen-design-opening',
            'title' => '厨房設計・開業支援',
            'title_en' => 'KITCHEN DESIGN & OPENING SUPPORT',
            'lead' => '現地調査から厨房設計、機器選定、搬入設置まで。お店づくりを一つの窓口で支えます。',
            'intro' => '飲食店の厨房は、機器を並べるだけでは完成しません。スタッフの動き、メニュー、提供数、将来の設備更新まで見据え、経営と現場の両方に合う厨房をご提案します。',
            'sections' => [
                ['heading'=>'現地を知ることから始めます','body'=>'店舗の広さ、設備条件、給排水、電気、ガス、排気などを確認し、計画の土台を整えます。','image'=>''],
                ['heading'=>'動きやすい厨房レイアウト','body'=>'調理工程とスタッフの動線を整理し、無駄な移動を減らす厨房レイアウトを設計します。','image'=>''],
                ['heading'=>'CAD図面で具体化','body'=>'打ち合わせ内容を図面に落とし込み、機器寸法や作業スペースを事前に確認できる状態にします。','image'=>''],
                ['heading'=>'予算に合う機器選定','body'=>'新品と中古を組み合わせ、初期投資と長期運用のバランスを考えた設備をご提案します。','image'=>''],
                ['heading'=>'搬入・設置、開業まで伴走','body'=>'機器の搬入設置や工事連携まで一括して進行し、安心して開業日を迎えられるよう支えます。','image'=>''],
            ],
            'published' => true,
        ]],
    ];
    foreach ($defaults as $name => $value) {
        $path = content_path($name);
        if (!is_file($path)) save_content($name, $value);
    }
    $company = load_content('company');
    if (($company[0]['company_name_en'] ?? '') === 'PRO KITCHEN HIT OKINAWA') {
        $company[0]['company_name_en'] = 'PRO CHUBO HIT OKINAWA';
        save_content('company', $company);
    }
    if (($company[0]['company_name'] ?? '') === 'プロ厨房HIT沖縄') {
        $company[0]['company_name'] = '株式会社 プロ厨房HIT 沖縄';
        save_content('company', $company);
    }
    if (!is_file(content_path('hero_settings'))) {
        $firstHero = load_content('hero')[0] ?? [];
        save_content('hero_settings', [[
            'id' => 'hero-settings',
            'overlay' => $firstHero['overlay'] ?? '#102a43',
            'overlay_opacity' => (int)($firstHero['overlay_opacity'] ?? 35),
            'dots' => !empty($firstHero['dots']),
            'dots_opacity' => (int)($firstHero['dots_opacity'] ?? 18),
        ]]);
    }
    $serviceSeeds = [
        ['id'=>'kitchen-design-opening','title'=>'厨房設計・開業支援','title_en'=>'KITCHEN DESIGN & OPENING SUPPORT','lead'=>'現地調査から厨房設計、機器選定、搬入設置まで。お店づくりを一つの窓口で支えます。','intro'=>'飲食店の厨房は、機器を並べるだけでは完成しません。スタッフの動き、メニュー、提供数、将来の設備更新まで見据えてご提案します。','headings'=>['現地を知ることから始めます','動きやすい厨房レイアウト','CAD図面で具体化','予算に合う機器選定','搬入・設置、開業まで伴走']],
        ['id'=>'equipment-sales-purchase','title'=>'厨房機器 販売・買取','title_en'=>'EQUIPMENT SALES & PURCHASE','lead'=>'新品・中古の販売から、設備更新や閉店時の買取まで柔軟に対応します。','intro'=>'全国のプロ厨房HITネットワークを活かし、予算と用途に合う厨房機器を選定。導入後や入替えまで長く支えます。','headings'=>['必要な機器を整理','新品と中古を最適に選定','全国ネットワークで調達','搬入・設置まで対応','入替え・買取も相談']],
        ['id'=>'interior-exterior','title'=>'内装・外装工事','title_en'=>'INTERIOR & EXTERIOR CONSTRUCTION','lead'=>'厨房だけでなく、客席や外観まで。お店全体を一つのコンセプトで形にします。','intro'=>'厨房計画と店舗デザイン、設備工事を分断せず、営業しやすくお客様に選ばれる空間をつくります。','headings'=>['現地調査とご要望確認','店舗コンセプトを整理','厨房と客席を一体設計','内装・外装工事を進行','完成・引き渡し']],
        ['id'=>'uriten','title'=>'ウリテン事業','title_en'=>'URITEN BUSINESS','lead'=>'居抜き物件を探す方、店舗を譲りたい方。次の営業へ想いをつなぎます。','intro'=>'店舗紹介、売却相談、設備や工事の調整まで、飲食店経営者の視点で居抜き売買をサポートします。','headings'=>['ご希望・ご事情を確認','居抜き物件をご紹介','現地と設備を確認','契約・引き継ぎを支援','開業・売却後もサポート']],
        ['id'=>'okinawa-opening','title'=>'沖縄での飲食店開業サポート','title_en'=>'OPEN A RESTAURANT IN OKINAWA','lead'=>'沖縄でお店を始めたい。その構想を、物件探しから開業まで現地で支えます。','intro'=>'県内の方はもちろん、県外からの出店にも対応。沖縄の地域事情や物流、設備条件を踏まえて伴走します。','headings'=>['沖縄で実現したい構想を確認','出店エリア・物件探し','現地条件と予算を整理','店舗・厨房づくり','開業準備を現地で支援']],
        ['id'=>'rational','title'=>'ラショナル製品の導入支援','title_en'=>'RATIONAL INTRODUCTION SUPPORT','lead'=>'現役ユーザーだから伝えられる、実際の使い勝手と導入効果があります。','intro'=>'スチームコンベクションオーブンやiVarioを実際に使う経験から、メニューとオペレーションに合う導入をご提案します。','headings'=>['現在の調理工程を確認','導入機種と運用を検討','実体験をもとにご説明','設置・立ち上げを支援','導入後の活用をフォロー']],
    ];
    $services = load_content('services');
    $existingServiceIds = array_column($services, 'id');
    foreach ($serviceSeeds as $seed) {
        if (in_array($seed['id'], $existingServiceIds, true)) continue;
        $sections = array_map(fn(string $heading): array => ['heading'=>$heading,'body'=>'この内容は管理画面から編集できます。サービスの具体的な流れや強みを掲載してください。','image'=>''], $seed['headings']);
        unset($seed['headings']); $seed['sections'] = $sections; $seed['published'] = true; $services[] = $seed;
    }
    $serviceDetails = [
        'kitchen-design-opening' => [
            'intro' => '飲食店の厨房は、機器を並べるだけでは完成しません。メニュー、席数、スタッフの動き、提供時間、将来の設備更新まで見据え、開業後も使いやすい厨房を計画します。構想段階のご相談から図面作成、機器選定、工事、搬入設置まで、一つの窓口でお店づくりを支えます。',
            'sections' => [
                ['現地調査から始める', '店舗の広さや設備条件、給排水・電気・ガス・換気の状況を現地で確認し、計画の土台を整えます。物件契約前の段階でも、希望する業態に適した設備容量があるか、追加工事がどの程度必要かを確認し、開業後に困らないための判断材料をご案内します。'],
                ['動きやすい厨房レイアウト', '仕込み、調理、盛り付け、提供、洗浄までの工程とスタッフの動線を整理し、移動や作業の無駄を抑えた厨房レイアウトをご提案します。ピーク時の人数や料理の提供順まで想定し、限られたスペースを安全かつ効率よく使える配置を考えます。'],
                ['図面と見積りで具体化', '厨房機器の寸法や作業スペース、設備接続位置を図面に落とし込み、完成後の姿を具体化します。新品と中古を適切に組み合わせ、初期費用だけでなく耐用年数やメンテナンス性も考慮しながら、無理のない予算に整えます。'],
                ['搬入・設置から開業まで', '厨房機器の手配と搬入設置、給排水・電気・ガスなどの設備工事との連携、試運転までまとめて進行します。工事中の変更や現場で生じる課題にも対応し、各機器が安全に稼働することを確認して、安心して開業日を迎えられるよう伴走します。'],
            ],
        ],
        'equipment-sales-purchase' => [
            'intro' => '新品・中古の厨房機器を、業態や予算、設置条件に合わせてご提案します。必要以上に機器を増やすのではなく、メニューと営業規模に合う構成を一緒に検討します。買い替えや閉店に伴う買取、撤去についても一つの窓口で対応します。',
            'sections' => [
                ['必要な機器を一緒に整理', '提供するメニュー、想定客数、営業時間、厨房の広さを確認し、本当に必要な機器と導入の優先順位を整理します。既存機器を活かせる場合はその方法も検討し、過剰な設備投資を避けながら、営業に必要な能力を確保します。'],
                ['新品と中古を最適に組み合わせる', '性能、使用頻度、耐用年数、保証、予算のバランスを考え、新品と中古から無理のない構成をご提案します。長く使う主要機器とコストを抑えられる機器を見極め、開業時の負担と将来の入れ替えまで考えた選定を行います。'],
                ['搬入・設置・買取まで対応', '納品時の搬入経路や設置条件を事前に確認し、接続・試運転まで現場に合わせて対応します。機器の入れ替えに伴う撤去や、閉店時に不要となった厨房機器の査定・買取もご相談いただけるため、購入から手放す時まで一貫してお任せいただけます。'],
            ],
        ],
        'interior-exterior' => [
            'intro' => '厨房だけでなく、客席やファサードまで店舗全体を一つのコンセプトで整えます。料理やサービスの魅力が伝わるデザインと、スタッフが働きやすい機能性の両方を大切にし、営業のしやすさとお客様に選ばれる空間づくりを両立します。',
            'sections' => [
                ['現地とご要望を確認', '物件の状態、業態、ターゲット、席数、ご予算、開業希望日を伺い、必要な工事と優先順位を整理します。既存の内装や設備で活かせる部分も確認し、費用をかける場所と抑える場所を明確にしながら計画を進めます。'],
                ['店舗コンセプトを設計', '料理やサービスの魅力がお客様に伝わるよう、客席、照明、素材、色、サイン、外観まで一貫した方向性をつくります。見た目の印象だけでなく、清掃やメンテナンスのしやすさも考え、長く使える店舗デザインをご提案します。'],
                ['厨房と客席を一体で計画', 'スタッフの作業動線とお客様の入店から退店までの流れを考え、厨房の作業性と客席の快適性を両立するレイアウトに整えます。配膳や片付けの距離、レジの位置、待合スペースなども含め、日々の営業がスムーズになる空間を設計します。'],
                ['内装・外装工事を進行', '大工、電気、給排水、空調、塗装、サインなど各職種と連携し、工程と品質を管理しながら工事を進めます。厨房機器の搬入時期も含めて全体を調整し、開業スケジュールに沿って完成・引き渡しまで責任を持って対応します。'],
            ],
        ],
        'uriten' => [
            'intro' => '居抜き店舗を手放したい方と、次のお店を始めたい方をつなぎます。内装や厨房設備を次の方へ引き継ぐことで、売る側・始める側双方の負担を抑えられる可能性があります。現役の飲食店経営者として、物件の紹介だけでなく工事や開業準備まで支援します。',
            'sections' => [
                ['売却・掲載のご相談', '店舗を手放したい方から、希望時期や設備、賃貸契約の状況などを伺い、内装や厨房機器を活かした居抜き売却の進め方をご案内します。営業への影響にも配慮しながら情報を整理し、次の利用者へ店舗の価値が伝わる掲載をお手伝いします。'],
                ['居抜き物件をご紹介', 'これから開業したい方へ、希望する業態、エリア、広さ、予算に合わせた物件情報をご紹介します。既存設備を活用できれば開業費用や準備期間を抑えられるため、物件の特徴と希望する店舗との相性を飲食店目線で一緒に検討します。'],
                ['現地と設備を確認', '内装や厨房設備の状態、電気・ガス・給排水の条件、修繕や追加工事の必要性を現地で確認します。物件取得費だけで判断せず、引き継いだ後に必要となる機器や工事の費用も含めて整理し、無理のない開業計画につなげます。'],
                ['契約後の開業もサポート', '厨房機器の追加・入れ替え、レイアウト変更、内外装工事など、物件取得後に必要な準備まで対応します。居抜きの良さを活かしながら新しいお店に合う形へ整え、引き渡しから開業まで複数の窓口を回らず進められるよう支えます。'],
            ],
        ],
        'okinawa-opening' => [
            'intro' => '沖縄本島はもちろん、宮古島・石垣島など離島での飲食店開業にも対応します。地域ごとの環境、物流、観光シーズン、設備条件を知る現地チームが、県内の方はもちろん県外企業や移住を伴う出店も支援します。',
            'sections' => [
                ['沖縄で実現したい構想を整理', '業態、出店時期、予算、希望エリア、想定するお客様を伺い、沖縄での開業に必要な準備を整理します。まだ物件や事業計画が固まっていない段階でも、現地で確認すべきことや今後の進め方を共有し、構想を具体的な計画へ変えていきます。'],
                ['物件・地域情報を共有', '居抜き店舗を含む物件情報や、観光需要、地域住民の生活圏、季節による人の動きなどを踏まえた出店エリア選びをサポートします。物件ごとの設備条件や物流面も確認し、希望する業態を無理なく運営できる場所かを一緒に検討します。'],
                ['厨房と店舗を現地で計画', '本土側の本部・担当者とも連携し、厨房機器、レイアウト、内外装工事を沖縄の現地条件に合わせて進めます。塩害や高温多湿への配慮、機器の納期や輸送、工事業者との調整まで見据え、開業準備が滞らない体制を整えます。'],
                ['本島・離島の開業に伴走', '沖縄本島に加え、宮古島や石垣島など離島でのプロジェクトにも対応します。現地確認、機器の搬入設置、工事、試運転まで工程をつなぎ、遠方からの出店でも状況を共有しながら、安心して開業日を迎えられるよう伴走します。'],
            ],
        ],
        'rational' => [
            'intro' => 'スチームコンベクションオーブンをはじめとするラショナル製品を、実際のメニューとオペレーションに合わせてご提案します。単に機器を導入するのではなく、調理品質の安定、省力化、仕込み時間の短縮につながる使い方まで一緒に考えます。',
            'sections' => [
                ['現在の調理工程を確認', '仕込み、加熱、保温、提供までの流れや、時間帯ごとの調理量、スタッフ数を伺い、現在の課題を整理します。焼成のばらつき、作業の属人化、仕込み時間などを確認し、ラショナル製品の導入によって改善できる工程を見つけます。'],
                ['最適な機種と運用をご提案', '調理量、メニュー、設置スペース、電気・給排水などの設備条件に合わせて機種を選定します。既存の調理機器との役割分担も考え、導入後に厨房全体が使いやすくなり、機器の能力を十分に活かせる運用をご提案します。'],
                ['実演を通して効果を確認', '導入前に実際のメニューや調理をイメージしながら、仕上がり、再現性、作業時間を具体的に確認します。経験だけに頼らず一定の品質を保てるか、省力化や提供スピードの改善につながるかを確かめ、納得したうえで導入を検討できます。'],
                ['設置後の活用をフォロー', '搬入・設置して終わりではなく、スタッフが日常の営業で機能を使いこなせるよう活用を支援します。メニュー変更や調理量の増加に合わせた運用の見直しも行い、導入効果を継続して高められるようフォローします。'],
            ],
        ],
    ];
    foreach ($services as &$service) {
        $detail = $serviceDetails[$service['id'] ?? ''] ?? null;
        if (!$detail || (int)($service['content_revision'] ?? 0) >= 2) continue;
        $service['intro'] = $detail['intro'];
        for ($index = 0; $index < 5; $index++) {
            $copy = $detail['sections'][$index] ?? ['', ''];
            $service['sections'][$index] = [
                'heading' => $copy[0],
                'body' => $copy[1],
                'image' => (string)($service['sections'][$index]['image'] ?? ''),
                'enabled' => isset($detail['sections'][$index]),
            ];
        }
        $service['content_revision'] = 2;
    }
    unset($service);
    save_content('services', $services);
}

function content_path(string $name): string { return DATA_DIR . '/content/' . preg_replace('/[^a-z0-9_-]/i', '', $name) . '.json'; }
function load_content(string $name): array
{
    $json = @file_get_contents(content_path($name));
    $data = $json ? json_decode($json, true) : [];
    return is_array($data) ? $data : [];
}
function save_content(string $name, array $data): void
{
    $path = content_path($name);
    $temp = $path . '.tmp';
    file_put_contents($temp, json_encode(array_values($data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    rename($temp, $path);
}
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(24)); }
function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('セッションが切れました。前の画面へ戻ってやり直してください。');
    }
}
function admin_config(): array
{
    $path = DATA_DIR . '/admin.json';
    $data = is_file($path) ? json_decode((string)file_get_contents($path), true) : [];
    return is_array($data) ? $data : [];
}
function save_admin(array $data): void { file_put_contents(DATA_DIR . '/admin.json', json_encode($data, JSON_PRETTY_PRINT), LOCK_EX); }
function is_admin(): bool { return !empty($_SESSION['admin']); }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function published(array $items): array { return array_values(array_filter($items, fn($item) => !empty($item['published']))); }
function inquiry_types(): array
{
    return ['開業相談', '厨房設計・厨房機器', '内装・外装工事', '機器の買取・店舗売却', 'その他'];
}
function save_inquiry(array $inquiry): void
{
    $items = load_content('inquiries');
    array_unshift($items, $inquiry);
    save_content('inquiries', array_slice($items, 0, 1000));
}
function notify_inquiry(array $inquiry, string $recipient): bool
{
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) return false;
    $host = preg_replace('/[^a-z0-9.-]/i', '', (string)($_SERVER['HTTP_HOST'] ?? 'prohit-okinawa.com'));
    $subject = '【' . APP_NAME . '】お問い合わせが届きました';
    $body = "Webサイトからお問い合わせが届きました。\n\n"
        . "受付番号: {$inquiry['id']}\n"
        . "相談種別: {$inquiry['type']}\n"
        . "お名前: {$inquiry['name']}\n"
        . "会社・店舗名: {$inquiry['company']}\n"
        . "電話番号: {$inquiry['phone']}\n"
        . "メール: {$inquiry['email']}\n\n"
        . "お問い合わせ内容:\n{$inquiry['message']}\n\n"
        . "受信日時: {$inquiry['created_at']}\n";
    $headers = [
        'From: ' . APP_NAME . ' <no-reply@' . $host . '>',
        'Reply-To: ' . $inquiry['email'],
        'Content-Type: text/plain; charset=UTF-8',
    ];
    return @mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}
function upload_image(string $field, string $current = ''): string
{
    if (empty($_FILES[$field]['tmp_name'])) return $current;
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 6 * 1024 * 1024) throw new RuntimeException('画像は6MB以下にしてください。');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) throw new RuntimeException('JPEG、PNG、WebPのみ利用できます。');
    return store_uploaded_image((string)$file['tmp_name'], $mime);
}

function store_uploaded_image(string $temporary, string $mime): string
{
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $name = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    $destination = UPLOAD_DIR . '/' . $name;
    $size = @getimagesize($temporary);
    if (!$size || empty($size[0]) || empty($size[1])) throw new RuntimeException('画像のサイズを確認できませんでした。');
    $width = (int)$size[0];
    $height = (int)$size[1];
    if (max($width, $height) <= UPLOAD_IMAGE_MAX_EDGE) {
        if (!move_uploaded_file($temporary, $destination)) throw new RuntimeException('画像を保存できませんでした。');
        return 'uploads/' . $name;
    }
    $loaders = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];
    $loader = $loaders[$mime];
    if (!function_exists($loader) || !function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('画像の自動縮小を利用できません。サーバーの画像処理設定を確認してください。');
    }
    $source = @$loader($temporary);
    if (!$source) throw new RuntimeException('画像を読み込めませんでした。');
    $scale = UPLOAD_IMAGE_MAX_EDGE / max($width, $height);
    $resizedWidth = max(1, (int)round($width * $scale));
    $resizedHeight = max(1, (int)round($height * $scale));
    $resized = imagecreatetruecolor($resizedWidth, $resizedHeight);
    if ($mime !== 'image/jpeg') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
    }
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $width, $height);
    $saved = match ($mime) {
        'image/jpeg' => imagejpeg($resized, $destination, 85),
        'image/png' => imagepng($resized, $destination, 6),
        'image/webp' => imagewebp($resized, $destination, 85),
    };
    imagedestroy($source);
    imagedestroy($resized);
    if (!$saved) throw new RuntimeException('画像を保存できませんでした。');
    return 'uploads/' . $name;
}

function upload_image_files(string $field, int $limit): array
{
    if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) return [];
    $saved = [];
    $count = count($_FILES[$field]['name']);
    $selectedCount = count(array_filter($_FILES[$field]['name'], fn($name) => (string)$name !== ''));
    if ($selectedCount > $limit) throw new RuntimeException('施工写真は合計10枚までです。');
    for ($index = 0; $index < $count && count($saved) < $limit; $index++) {
        $error = (int)($_FILES[$field]['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) continue;
        if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('画像のアップロードに失敗しました。');
        $size = (int)($_FILES[$field]['size'][$index] ?? 0);
        $temporary = (string)($_FILES[$field]['tmp_name'][$index] ?? '');
        if ($size < 1 || $size > 6 * 1024 * 1024) throw new RuntimeException('画像は1枚6MB以下にしてください。');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) throw new RuntimeException('JPEG、PNG、WebPのみ利用できます。');
        $saved[] = store_uploaded_image($temporary, $mime);
    }
    return $saved;
}

function work_images(array $work): array
{
    $images = array_values(array_filter($work['images'] ?? [], 'is_string'));
    if ($images === [] && !empty($work['image'])) $images[] = (string)$work['image'];
    return array_slice($images, 0, 10);
}

boot_app();
