@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h1>歌詞タイムライン</h1><a href="{{ route('admin.karaoke.index') }}">一覧へ戻る</a></div>
    @if (session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    <form method="POST" action="{{ route('admin.karaoke.timeline', $song_id) }}">@csrf @method('PUT')
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>歌詞</th><th>開始(ms)</th><th>終了(ms)</th><th>信頼度</th></tr></thead><tbody>
        @foreach ($segments as $segment)
            <tr>
                <td>{{ $segment['position'] }}<input type="hidden" name="segments[{{ $loop->index }}][position]" value="{{ $segment['position'] }}"></td>
                <td><input class="form-control" name="segments[{{ $loop->index }}][text]" value="{{ $segment['text'] }}" required></td>
                <td><input class="form-control" type="number" name="segments[{{ $loop->index }}][start_ms]" value="{{ $segment['start_ms'] }}" required></td>
                <td><input class="form-control" type="number" name="segments[{{ $loop->index }}][end_ms]" value="{{ $segment['end_ms'] }}" required></td>
                <td>{{ number_format($segment['confidence'] * 100, 0) }}%</td>
            </tr>
        @endforeach
        </tbody></table></div>
        <button class="btn btn-primary">タイムラインを保存</button>
    </form>
</div>
@endsection
