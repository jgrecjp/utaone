@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">カラオケデータ管理</h1>

    @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    @if ($apiError) <div class="alert alert-danger">{{ $apiError }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header">新しい楽曲</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.karaoke.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label">曲名</label><input class="form-control" name="title" value="{{ old('title') }}" required></div>
                    <div class="col-md-5"><label class="form-label">アーティスト</label><input class="form-control" name="artist" value="{{ old('artist') }}" required></div>
                    <div class="col-md-2"><label class="form-label">難易度</label><input class="form-control" type="number" min="1" max="10" name="difficulty" value="{{ old('difficulty', 1) }}" required></div>
                    @foreach (['original' => '元音源', 'instrumental' => 'カラオケ音源', 'vocal' => 'アカペラ音源'] as $name => $label)
                        <div class="col-md-4"><label class="form-label">{{ $label }}（WAV/MP3）</label><input class="form-control" type="file" name="{{ $name }}" accept="audio/wav,audio/mpeg,.wav,.mp3" required></div>
                    @endforeach
                    <div class="col-md-4"><label class="form-label">歌詞（TXT/UTF-8）</label><input class="form-control" type="file" name="lyrics" accept="text/plain,.txt" required></div>
                </div>
                <button class="btn btn-primary mt-3">登録して解析開始</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">登録済み楽曲</div>
        <div class="table-responsive">
            <table class="table mb-0"><thead><tr><th>ID</th><th>曲名</th><th>アーティスト</th><th>状態</th><th></th></tr></thead>
            <tbody>
            @forelse ($songs as $song)
                <tr><td>{{ $song['id'] }}</td><td>{{ $song['title'] }}</td><td>{{ $song['artist'] }}</td><td><code>{{ $song['status'] }}</code></td>
                    <td class="text-end">
                        @if (in_array($song['status'], ['review_required', 'published']))
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.karaoke.review', $song['id']) }}">タイムライン確認</a>
                        @endif
                        @if ($song['status'] === 'review_required')
                            <form class="d-inline" method="POST" action="{{ route('admin.karaoke.publish', $song['id']) }}">@csrf<button class="btn btn-sm btn-success">公開</button></form>
                        @endif
                    </td></tr>
            @empty <tr><td colspan="5" class="text-muted">楽曲はまだありません。</td></tr> @endforelse
            </tbody></table>
        </div>
    </div>
</div>
@endsection
