<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\Examples;
use App\Http\Controllers\Controller;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\Dashboard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class HomeController extends Controller
{

    public function test(Content $content) {
        return $content
            ->header('Dashboard')
            ->description('Description...')
            ->body(\Inertia\Inertia::render('CompanySellTable', [
            ])
                ->rootView('admin.ib-app')
                ->toResponse(request())
                ->content());
    }

    public function index(Content $content)
    {
        return $content
            ->header('Dashboard')
            ->description('Description...')
            ->body(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $params = [
                        'greeting' =>greetOfNowCn(),
                        'username' => Admin::user()->name,
                        'avatar' => Admin::user()->getAvatar(),
                    ];
                    $title = view('admin::dashboard.title',$params);
                    $column->row($title);
//                    $column->row(new Examples\Tickets());
                });

            });
    }
}
