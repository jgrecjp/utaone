@extends('layouts.okapp')

@section('content')
    <div class="card">
        <div class="card-header">ダッシュボード</div>
        <div class="card-body">
            <h2>ようこそ、{{ Auth::user()->name }}さん！</h2>
            <p>uta.oneを楽しんでください！</p>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title">曲を探す</h5>
                            <p class="card-text">数千の曲から選んで歌おう！</p>
                            <a href="{{ route('songs.index') }}" class="btn btn-primary">曲一覧へ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title">スコア履歴</h5>
                            <p class="card-text">あなたの過去のスコアをチェック！</p>
                            <a href="{{ route('user.scores') }}" class="btn btn-primary">スコア履歴へ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title">ランキング</h5>
                            <p class="card-text">トップスコアをチェック！</p>
                            <a href="#" class="btn btn-primary">ランキングへ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
