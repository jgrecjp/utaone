@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>曲一覧</span>
            @if (Auth::user()->is_admin)
                <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSongModal">曲を追加</a>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="input-group">
                        <input type="text" id="search-input" class="form-control" placeholder="曲名・アーティスト名で検索">
                        <button class="btn btn-outline-secondary" type="button" id="search-button">検索</button>
                    </div>
                </div>
            </div>

            <div class="row mt-3" id="songs-container">
                @foreach ($songs as $song)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="{{ $song->thumbnail ? asset('storage/' . $song->thumbnail) : asset('images/default-thumbnail.jpg') }}" class="card-img-top" alt="{{ $song->title }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $song->title }}</h5>
                                <p class="card-text">{{ $song->artist }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        難易度:
                                        @for ($i = 0; $i < $song->difficulty; $i++)
                                            ★
                                        @endfor
                                    </div>
                                    <a href="{{ route('songs.play', $song) }}" class="btn btn-primary">歌う</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>


