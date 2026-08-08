<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>楽曲一覧 | uta.one</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#6366f1',secondary:'#8b5cf6'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f9fafb;
        }
        .song-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .song-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .play-button {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .song-card:hover .play-button {
            opacity: 1;
        }
        input[type="search"]::-webkit-search-decoration,
        input[type="search"]::-webkit-search-cancel-button,
        input[type="search"]::-webkit-search-results-button,
        input[type="search"]::-webkit-search-results-decoration {
            display: none;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .custom-checkbox {
            display: none;
        }
        .custom-checkbox + label {
            position: relative;
            padding-left: 28px;
            cursor: pointer;
            display: inline-block;
        }
        .custom-checkbox + label:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            background: #fff;
            border-radius: 4px;
        }
        .custom-checkbox:checked + label:before {
            background: #6366f1;
            border-color: #6366f1;
        }
        .custom-checkbox:checked + label:after {
            content: '';
            position: absolute;
            left: 7px;
            top: 3px;
            width: 6px;
            height: 12px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="min-h-screen">
<div class="flex">
    <!-- サイドナビゲーション -->
    <aside class="sidebar fixed h-screen w-64 bg-white shadow-lg z-20 transition-transform lg:translate-x-0">
        <div class="p-4 border-b">
            <h1 class="font-['Pacifico'] text-2xl text-primary">uta.one</h1>
        </div>

        <div class="p-4 border-b">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <i class="ri-user-line text-gray-600"></i>
                </div>
                <div>
                    <p class="font-medium">ユーザー名</p>
                    <p class="text-xs text-gray-500">プレミアムユーザー</p>
                </div>
            </div>
        </div>

        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-100 text-gray-700">
                        <div class="w-6 h-6 flex items-center justify-center mr-3">
                            <i class="ri-home-line"></i>
                        </div>
                        <span>ホーム</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded bg-gray-100 text-primary font-medium">
                        <div class="w-6 h-6 flex items-center justify-center mr-3">
                            <i class="ri-music-line"></i>
                        </div>
                        <span>楽曲一覧</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-100 text-gray-700">
                        <div class="w-6 h-6 flex items-center justify-center mr-3">
                            <i class="ri-playlist-line"></i>
                        </div>
                        <span>プレイリスト</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-100 text-gray-700">
                        <div class="w-6 h-6 flex items-center justify-center mr-3">
                            <i class="ri-heart-line"></i>
                        </div>
                        <span>お気に入り</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center p-2 rounded hover:bg-gray-100 text-gray-700">
                        <div class="w-6 h-6 flex items-center justify-center mr-3">
                            <i class="ri-history-line"></i>
                        </div>
                        <span>履歴</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="p-4 mt-6">
            <h3 class="font-medium text-gray-700 mb-3">おすすめプレイリスト</h3>
            <ul class="space-y-3">
                <li>
                    <a href="#" class="flex items-center text-sm text-gray-600 hover:text-primary">
                        <div class="w-5 h-5 flex items-center justify-center mr-2">
                            <i class="ri-playlist-line"></i>
                        </div>
                        <span>2025年春の新曲</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center text-sm text-gray-600 hover:text-primary">
                        <div class="w-5 h-5 flex items-center justify-center mr-2">
                            <i class="ri-playlist-line"></i>
                        </div>
                        <span>ドライブミュージック</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center text-sm text-gray-600 hover:text-primary">
                        <div class="w-5 h-5 flex items-center justify-center mr-2">
                            <i class="ri-playlist-line"></i>
                        </div>
                        <span>作業用BGM</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center text-sm text-gray-600 hover:text-primary">
                        <div class="w-5 h-5 flex items-center justify-center mr-2">
                            <i class="ri-playlist-line"></i>
                        </div>
                        <span>人気アニメソング</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- メインコンテンツ -->
    <div class="flex-1 ml-0 lg:ml-64">
        <!-- ヘッダー -->
        <header class="sticky top-0 z-10 bg-white shadow-sm">
            <div class="flex items-center justify-between p-4">
                <button class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-700" id="menuToggle">
                    <i class="ri-menu-line text-xl"></i>
                </button>

                <h1 class="text-xl font-semibold lg:hidden">楽曲一覧</h1>

                <div class="relative ml-auto mr-4 w-full max-w-md">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="search" class="w-full pl-10 pr-4 py-2 border-none rounded-full bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary text-sm" placeholder="曲名、アーティスト名で検索">
                </div>

                <div class="flex items-center space-x-4">
                    <button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100">
                        <i class="ri-notification-line text-gray-700"></i>
                    </button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100">
                        <i class="ri-settings-line text-gray-700"></i>
                    </button>
                </div>
            </div>

            <!-- フィルターオプション -->
            <div class="p-4 border-t border-gray-100 bg-white">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">並び順:</span>
                        <div class="relative">
                            <button class="flex items-center space-x-1 bg-white border border-gray-200 rounded-button px-3 py-1.5 text-sm !rounded-button whitespace-nowrap">
                                <span>人気順</span>
                                <div class="w-4 h-4 flex items-center justify-center">
                                    <i class="ri-arrow-down-s-line"></i>
                                </div>
                            </button>
                            <!-- ドロップダウンメニューは非表示 -->
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">ジャンル:</span>
                        <div class="relative">
                            <button class="flex items-center space-x-1 bg-white border border-gray-200 rounded-button px-3 py-1.5 text-sm !rounded-button whitespace-nowrap">
                                <span>すべて</span>
                                <div class="w-4 h-4 flex items-center justify-center">
                                    <i class="ri-arrow-down-s-line"></i>
                                </div>
                            </button>
                            <!-- ドロップダウンメニューは非表示 -->
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">年代:</span>
                        <div class="relative">
                            <button class="flex items-center space-x-1 bg-white border border-gray-200 rounded-button px-3 py-1.5 text-sm !rounded-button whitespace-nowrap">
                                <span>すべて</span>
                                <div class="w-4 h-4 flex items-center justify-center">
                                    <i class="ri-arrow-down-s-line"></i>
                                </div>
                            </button>
                            <!-- ドロップダウンメニューは非表示 -->
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="newRelease" class="custom-checkbox">
                        <label for="newRelease" class="text-sm">新着のみ</label>
                    </div>

                    <button class="ml-auto px-3 py-1.5 bg-primary text-white rounded-button text-sm flex items-center space-x-1 !rounded-button whitespace-nowrap">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-filter-line"></i>
                        </div>
                        <span>フィルター</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- 楽曲一覧 -->
        <main class="p-4 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- 楽曲カード1 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20pop%20music%20album%20cover%20with%20vibrant%20colors%2C%20minimalist%20design%2C%20featuring%20abstract%20shapes%20and%20patterns%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=1&orientation=landscape" alt="桜の季節" class="w-full h-48 object-cover object-top">
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">桜の季節</h3>
                                <p class="text-gray-600 text-sm">山田 花子</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.8</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>2.4万回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード2 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20rock%20band%20album%20cover%20with%20dark%20moody%20atmosphere%2C%20dramatic%20lighting%2C%20urban%20setting%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=2&orientation=landscape" alt="未来への道" class="w-full h-48 object-cover object-top">
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">未来への道</h3>
                                <p class="text-gray-600 text-sm">BLUE SKY</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.7</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>1.8万回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード3 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20electronic%20music%20album%20cover%20with%20futuristic%20design%2C%20neon%20colors%2C%20digital%20aesthetic%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=3&orientation=landscape" alt="夜の光" class="w-full h-48 object-cover object-top">
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">夜の光</h3>
                                <p class="text-gray-600 text-sm">NEON TOKYO</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.9</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>3.2万回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-fill text-primary"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード4 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20acoustic%20folk%20music%20album%20cover%20with%20natural%20scenery%2C%20warm%20colors%2C%20rustic%20aesthetic%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=4&orientation=landscape" alt="風の歌" class="w-full h-48 object-cover object-top">
                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">NEW</div>
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">風の歌</h3>
                                <p class="text-gray-600 text-sm">森田 一郎</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.6</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>8,500回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード5 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20jazz%20music%20album%20cover%20with%20sophisticated%20design%2C%20monochrome%20palette%2C%20elegant%20aesthetic%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=5&orientation=landscape" alt="夜のジャズ" class="w-full h-48 object-cover object-top">
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">夜のジャズ</h3>
                                <p class="text-gray-600 text-sm">高橋 ジャズトリオ</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.5</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>1.2万回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード6 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20anime%20soundtrack%20album%20cover%20with%20colorful%20illustration%2C%20fantasy%20elements%2C%20vibrant%20design%2C%20professional%20illustration%20with%20digital%20art%20style%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=6&orientation=landscape" alt="アニメヒーロー" class="w-full h-48 object-cover object-top">
                        <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">NEW</div>
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">アニメヒーロー</h3>
                                <p class="text-gray-600 text-sm">アニメスターズ</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.9</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>5.6万回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-fill text-primary"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード7 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20traditional%20music%20album%20cover%20with%20zen%20aesthetic%2C%20minimalist%20design%2C%20ink%20painting%20style%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=7&orientation=landscape" alt="和の心" class="w-full h-48 object-cover object-top">
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">和の心</h3>
                                <p class="text-gray-600 text-sm">伝統楽団</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.7</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>9,800回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード8 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20indie%20pop%20music%20album%20cover%20with%20retro%20aesthetic%2C%20vintage%20filter%2C%20nostalgic%20mood%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=8&orientation=landscape" alt="レトロ青春" class="w-full h-48 object-cover object-top">
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">レトロ青春</h3>
                                <p class="text-gray-600 text-sm">インディーズバンド</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.4</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>7,200回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 楽曲カード9 -->
                <div class="song-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="relative">
                        <img src="https://readdy.ai/api/search-image?query=Japanese%20hip%20hop%20music%20album%20cover%20with%20urban%20aesthetic%2C%20graffiti%20elements%2C%20street%20style%2C%20professional%20photography%20with%20studio%20lighting%2C%20high%20quality%20album%20artwork&width=400&height=300&seq=9&orientation=landscape" alt="東京ビート" class="w-full h-48 object-cover object-top">
                        <button class="play-button absolute right-3 bottom-3 w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-lg !rounded-full whitespace-nowrap">
                            <i class="ri-play-fill text-white text-xl"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-lg">東京ビート</h3>
                                <p class="text-gray-600 text-sm">MC タカシ</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-5 h-5 flex items-center justify-center text-yellow-500">
                                    <i class="ri-star-fill"></i>
                                </div>
                                <span class="text-sm font-medium">4.6</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <div class="w-4 h-4 flex items-center justify-center mr-1">
                                    <i class="ri-play-list-line"></i>
                                </div>
                                <span>1.5万回再生</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-heart-line text-gray-500"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 !rounded-full whitespace-nowrap">
                                    <i class="ri-add-line text-gray-500"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 無限スクロール用ローディング表示 -->
            <div class="flex justify-center mt-8 pb-8">
                <button class="px-6 py-2 bg-white border border-gray-200 rounded-button text-gray-700 hover:bg-gray-50 !rounded-button whitespace-nowrap">
                    もっと見る
                </button>
            </div>
        </main>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });

        // 画面外クリックでメニューを閉じる
        document.addEventListener('click', function(event) {
            if (sidebar.classList.contains('open') &&
                !sidebar.contains(event.target) &&
                event.target !== menuToggle) {
                sidebar.classList.remove('open');
            }
        });
    });
</script>
</body>
</html>
