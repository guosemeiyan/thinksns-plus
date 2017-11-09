<?php

namespace Zhiyi\Plus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Zhiyi\Plus\Http\Controllers\Controller;
use Zhiyi\Plus\Models\Sensitive as SensitiveModel;
use Zhiyi\Plus\Http\Requests\Admin\CreateSensitive as CreateSensitiveRequest;
use Zhiyi\Plus\Http\Requests\Admin\UpdateSensitive as UpdateSensitiveRequest;

class SensitiveController extends Controller
{
    /**
     * List all sensitives.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 15);
        $offset = (int) $request->query('offset', 0);
        $type = $request->query('type');
        $word = $request->query('word');

        $sensitives = SensitiveModel::when(in_array($type, ['warning', 'replace']), function ($query) use ($type) {
            return $query->where('type', $type);
        })
        ->when((bool) $word, function ($query) use ($word) {
            return $query->where('word', 'like', sprintf('%%%s%%', $word));
        })
        ->limit($limit)
        ->offset($offset)
        ->get();

        return response()->json($sensitives, 200);
    }

    /**
     * Create a sensitive.
     *
     * @param \Zhiyi\Plus\Http\Requests\Admin\CreateSensitive $request
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function store(CreateSensitiveRequest $request)
    {
        $word = $request->input('word');
        $type = $request->input('type');
        $replace = $request->input('replace');

        $sensitive = new SensitiveModel();
        $sensitive->word = $word;
        $sensitive->type = $type;
        $sensitive->replace = $type === 'replace' ? $replace : null;
        $sensitive->save();

        return response(['message' => '添加成功！', 'sensitive' => $sensitive], 201);
    }

    /**
     * Uodate a sensitive.
     *
     * @param \Zhiyi\Plus\Http\Requests\Admin\UpdateSensitive $request
     * @param \Zhiyi\Plus\Models\Sensitive $sensitive
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function update(UpdateSensitiveRequest $request, SensitiveModel $sensitive)
    {
        $word = $request->input('word');
        $type = $request->input('type');
        $replace = $request->input('replace');

        if ($word !== $sensitive->word && SensitiveModel::where('word', $word)->first()) {
            return response()->json(['message' => '敏感词已存在！'], 422);
        }

        $sensitive->word = $word;
        $sensitive->type = $type;
        $sensitive->replace = $type === 'replace' ? $replace : null;
        $sensitive->save();

        return response()->json(['message' => '修改成功', 'sensitive' => $sensitive], 201);
    }
}
