<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = \App\Models\SystemSetting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function update(Request $request, $key)
    {
        $request->validate([
            'value' => 'required|array',
        ]);

        $setting = \App\Models\SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $request->value]
        );

        return response()->json([
            'message' => 'Setting updated successfully',
            'setting' => $setting
        ]);
    }
}
