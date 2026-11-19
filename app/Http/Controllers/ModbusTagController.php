<?php

namespace App\Http\Controllers;

use App\Models\ModbusTag;
use Illuminate\Http\Request;

class ModbusTagController extends Controller
{
    /**
     * タグ一覧画面の表示
     */
    public function index()
    {
        $tags = ModbusTag::paginate(10);
        return view('modbus.tags.index', compact('tags'));
    }

    /**
     * 新規登録画面の表示
     */
    public function create()
    {
        return view('modbus.tags.create');
    }

    /**
     * 新規タグの保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_code'         => ['required', 'string', 'max:255', 'unique:modbus_tags,tag_code'],
            'name'             => ['required', 'string', 'max:255'],
            'register_address' => ['required', 'integer', 'min:0'],
            'unit'             => ['nullable', 'string', 'max:50'],
            'data_type'        => ['nullable', 'string', 'max:50'],
            'high_limit'       => ['nullable', 'numeric'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        ModbusTag::create($validated);

        return redirect()->route('modbus.tags.index')
                         ->with('success', '新しいModbusタグを登録しました。');
    }

    /**
     * 🔥【追加】個別編集画面の表示
     */
    public function edit(ModbusTag $tag)
    {
        return view('modbus.tags.edit', compact('tag'));
    }

    /**
     * タグ情報の更新
     */
    public function update(Request $request, ModbusTag $tag)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'register_address' => ['required', 'integer', 'min:0'],
            'unit'             => ['nullable', 'string', 'max:50'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $tag->update($validated);

        return redirect()->route('modbus.tags.index')
                         ->with('success', 'Modbusタグを更新しました。');
    }

    /**
     * タグの削除
     */
    public function destroy(ModbusTag $tag)
    {
        $tag->delete();

        return redirect()->route('modbus.tags.index')
                         ->with('success', 'Modbusタグを削除しました。');
    }
}