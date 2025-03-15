<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessExcelImportJob;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ImportController extends Controller
{
    /**
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function showUploadForm()
    {
        return view('import.upload');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function import(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('imports');

        $redisKey = 'excel_import_progress:' . uniqid('', true);
        Redis::set($redisKey, 0);

        ProcessExcelImportJob::dispatch(storage_path('app/private/' . $filePath), $redisKey);

        return response()->json([
            'message'   => 'Импорт запущен',
            'redis_key' => $redisKey,
        ]);
    }
}
