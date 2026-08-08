<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>uta.one - 歌唱戦力</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#8257E5',secondary:'#FF5E84'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body {
            font-family: 'Noto Sans JP', sans-serif;
        }
        .hero-section {
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0.8) 40%, rgba(0, 0, 0, 0.4)), url('/img/back.jpg');
            background-size: cover;
            background-position: center;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .song-card:hover {
            transform: scale(1.03);
        }
        .cta-section {
            background-image: linear-gradient(to right, rgba(130, 87, 229, 0.9), rgba(255, 94, 132, 0.9)), url('/img/obi.jpg');
            background-size: cover;
            background-position: center;
        }
        input:focus {
            outline: none;
        }
    </style>
</head>
<body class="bg-gray-50">
<!-- Header Section -->
<header class="bg-white shadow-sm fixed w-full z-50">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="#" class="text-3xl font-['Pacifico'] text-primary">uta.one</a>

        <nav class="hidden md:flex items-center space-x-6">
            <a href="/songs" class="text-gray-700 hover:text-primary transition-colors">楽曲一覧</a>
            <a href="#" class="text-gray-700 hover:text-primary transition-colors">ランキング</a>
            <a href="/recordings" class="text-gray-700 hover:text-primary transition-colors">マイページ</a>
            <a href="#" class="text-gray-700 hover:text-primary transition-colors">ヘルプ</a>
        </nav>

        @if (Route::has('login'))
        <div class="flex items-center space-x-3">
            @auth
                <a href="{{ url('/home') }}" class="text-primary font-medium hover:text-primary/80 transition-colors !rounded-button whitespace-nowrap">Home</a>
            @else
                <a href="{{ route('login') }}" class="text-primary font-medium hover:text-primary/80 transition-colors !rounded-button whitespace-nowrap">Log in</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="bg-primary text-white px-4 py-2 font-medium hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">Register</a>
                @endif
            @endauth
        </div>
        @endif
    </div>
</header>

<!-- Hero Section -->
<section class="hero-section w-full h-screen flex items-center pt-16">
    <div class="container mx-auto px-4 w-full">
        <div class="max-w-2xl text-white">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">あなたの歌声を、いつでもどこでも</h1>
            <p class="text-xl md:text-2xl mb-8">プロフェッショナルな音質で本格カラオケ体験。<br>好きな曲を選んで、いつでも歌える。</p>
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <button class="bg-primary text-white px-8 py-4 text-lg font-medium hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">無料で始める</button>
                <button class="bg-white text-gray-900 px-8 py-4 text-lg font-medium hover:bg-gray-100 transition-colors !rounded-button whitespace-nowrap">詳しく見る</button>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">uta.oneの特徴</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">プロフェッショナルなカラオケ体験をあなたに。いつでもどこでも、高品質な音源で歌えます。</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="feature-card bg-white p-8 rounded shadow-lg text-center transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="ri-music-line text-primary ri-2x"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">豊富な楽曲選択</h3>
                <p class="text-gray-600">10,000曲以上の最新ヒット曲から定番曲まで、幅広いジャンルの楽曲をご用意しています。</p>
            </div>

            <div class="feature-card bg-white p-8 rounded shadow-lg text-center transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="ri-volume-up-line text-primary ri-2x"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">高品質な音源</h3>
                <p class="text-gray-600">プロフェッショナルな音質で、スタジオ録音のような本格的なカラオケ体験をお楽しみいただけます。</p>
            </div>

            <div class="feature-card bg-white p-8 rounded shadow-lg text-center transition-all duration-300">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="ri-medal-line text-primary ri-2x"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">歌唱採点機能</h3>
                <p class="text-gray-600">AIによる歌唱分析で、あなたの歌をリアルタイムで採点。上達を実感できます。</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Songs Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-10">
            <h2 class="text-3xl font-bold">人気楽曲</h2>
            <a href="/songs" class="text-primary flex items-center hover:text-primary/80 transition-colors">
                もっと見る
                <i class="ri-arrow-right-line ml-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @foreach ($songs as $song)
                <a href="{{ route('songs.play', $song) }}" class="btn btn-primary">
            <div class="song-card bg-white rounded overflow-hidden shadow-md transition-all duration-300">
                <div class="relative pb-[100%]">
                    <img src="{{ $song->thumbnail ? asset('storage/' . $song->thumbnail) : asset('images/default-thumbnail.jpg') }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $song->title }}">
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-gray-900 truncate">{{ $song->title }}</h3>
                    <p class="text-gray-600 text-sm truncate">{{ $song->artist }}</p>
                </div>
            </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Detailed Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <!-- Feature 1 -->
        <div class="flex flex-col md:flex-row items-center mb-20">
            <div class="md:w-1/2 mb-10 md:mb-0 md:pr-10">
                <h2 class="text-3xl font-bold mb-6">どこでも歌える、いつでも楽しめる</h2>
                <p class="text-gray-600 mb-6">スマートフォン、タブレット、PCなど、様々なデバイスで利用できます。通勤中や自宅でのリラックスタイムなど、いつでもどこでもカラオケを楽しめます。</p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>インターネット環境があればすぐに歌える</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>マイクがなくてもスマホのマイクで歌える</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>オフライン再生にも対応</span>
                    </li>
                </ul>
            </div>
            <div class="md:w-1/2">
                <img src="/img/1st.jpg" alt="どこでも歌える" class="rounded-lg shadow-xl w-full h-auto">
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="flex flex-col md:flex-row-reverse items-center mb-20">
            <div class="md:w-1/2 mb-10 md:mb-0 md:pl-10">
                <h2 class="text-3xl font-bold mb-6">プロフェッショナルな音質</h2>
                <p class="text-gray-600 mb-6">スタジオ品質の音源を使用し、本格的なカラオケ体験を提供します。エコー、リバーブなどの効果も調整可能で、あなたの声を最高の状態で楽しめます。</p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>高品質なスタジオ録音音源</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>カスタマイズ可能な音声エフェクト</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>ボーカルキャンセル機能で原曲のボーカルも調整可能</span>
                    </li>
                </ul>
            </div>
            <div class="md:w-1/2">
                <img src="/img/2nd.jpg" alt="プロフェッショナルな音質" class="rounded-lg shadow-xl w-full h-auto">
            </div>
        </div>

        <!-- Feature 3 -->
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0 md:pr-10">
                <h2 class="text-3xl font-bold mb-6">歌唱力向上をサポート</h2>
                <p class="text-gray-600 mb-6">AIによる歌唱分析で、あなたの歌をリアルタイムで採点。音程、リズム、表現力などを細かく分析し、歌唱力の向上をサポートします。</p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>リアルタイム音程表示</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>詳細な歌唱分析レポート</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>上達のためのアドバイス機能</span>
                    </li>
                </ul>
            </div>
            <div class="md:w-1/2">
                <img src="/img/3rd.jpg" alt="歌唱力向上をサポート" class="rounded-lg shadow-xl w-full h-auto">
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-16">ユーザーの声</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="text-primary">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">「自宅でカラオケを楽しめるのが最高です。音質も良く、採点機能で自分の歌唱力が向上しているのを実感できます。」</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-200 rounded-full overflow-hidden mr-3">
                        <img src="/img/koe1.jpg" alt="田中 さくら" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="font-bold">田中 さくら</h4>
                        <p class="text-gray-500 text-sm">会社員 / 28歳</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="text-primary">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">「通勤中の電車でも練習できるのが便利です。音楽好きにはたまらないアプリです。曲のバリエーションも豊富で飽きません。」</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-200 rounded-full overflow-hidden mr-3">
                        <img src="/img/koe2.jpg" alt="鈴木 健太" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="font-bold">鈴木 健太</h4>
                        <p class="text-gray-500 text-sm">大学生 / 22歳</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="text-primary">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-fill"></i>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">「子供と一緒に楽しめるのが良いですね。家族で盛り上がれるし、最新曲もすぐに追加されるので、いつも新鮮です。」</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-200 rounded-full overflow-hidden mr-3">
                        <img src="/img/koe3.jpg" alt="佐々木 美穂" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="font-bold">佐々木 美穂</h4>
                        <p class="text-gray-500 text-sm">主婦 / 42歳</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-20 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-6">今すぐ歌を始めよう</h2>
        <p class="text-xl mb-10 max-w-2xl mx-auto">無料登録で5曲まで歌い放題。<br>プレミアム会員になれば10,000曲以上の楽曲が歌い放題に。</p>

        <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
            <button class="bg-white text-primary px-8 py-4 text-lg font-medium hover:bg-gray-100 transition-colors !rounded-button whitespace-nowrap">無料で始める</button>
            <button class="bg-transparent border-2 border-white text-white px-8 py-4 text-lg font-medium hover:bg-white/10 transition-colors !rounded-button whitespace-nowrap">プランを見る</button>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-xl mx-auto text-center">
            <h2 class="text-2xl font-bold mb-4">最新情報をお届けします</h2>
            <p class="text-gray-600 mb-6">新曲の追加やキャンペーン情報など、お得な情報をメールでお知らせします。</p>

            <div class="flex">
                <input type="email" placeholder="メールアドレスを入力" class="flex-1 px-4 py-3 border border-gray-300 rounded-l focus:border-primary !rounded-button whitespace-nowrap">
                <button class="bg-primary text-white px-6 py-3 rounded-r hover:bg-primary/90 transition-colors !rounded-button whitespace-nowrap">登録する</button>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-white pt-16 pb-8">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <div>
                <a href="#" class="text-3xl font-['Pacifico'] text-white mb-4 inline-block">uta.one</a>
                <p class="text-gray-400 mb-4">あなたの歌声を、いつでもどこでも。<br>本格カラオケアプリ。</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <div class="w-10 h-10 flex items-center justify-center">
                            <i class="ri-twitter-x-line ri-lg"></i>
                        </div>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <div class="w-10 h-10 flex items-center justify-center">
                            <i class="ri-instagram-line ri-lg"></i>
                        </div>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <div class="w-10 h-10 flex items-center justify-center">
                            <i class="ri-youtube-line ri-lg"></i>
                        </div>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <div class="w-10 h-10 flex items-center justify-center">
                            <i class="ri-tiktok-line ri-lg"></i>
                        </div>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold mb-4">サービス</h3>
                <ul class="space-y-2">
                    <li><a href="/list" class="text-gray-400 hover:text-white transition-colors">楽曲一覧</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">ランキング</a></li>
                    <li><a href="/list" class="text-gray-400 hover:text-white transition-colors">新着曲</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">ジャンル別</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">アーティスト別</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-bold mb-4">サポート</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">よくある質問</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">お問い合わせ</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">使い方ガイド</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">料金プラン</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">デバイス対応状況</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-bold mb-4">会社情報</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">会社概要</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">プライバシーポリシー</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">利用規約</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">特定商取引法に基づく表記</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">採用情報</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">© 2025 uta.one All Rights Reserved.</p>
                <div class="flex space-x-4">
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-apple-fill text-gray-400"></i>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-android-fill text-gray-400"></i>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-visa-fill text-gray-400"></i>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-mastercard-fill text-gray-400"></i>
                    </div>
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="ri-paypal-fill text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>
</body>
</html>
