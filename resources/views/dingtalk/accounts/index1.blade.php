<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>到账利润</title>
    <!-- Scripts -->



    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.14.1/dist/bootstrap-table.min.css">

    <script src="{{ mix('js/app.js') }}"></script>
    <script src="https://unpkg.com/bootstrap-table@1.14.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.14.1/dist/locale/bootstrap-table-zh-CN.min.js"></script>
    <style>
        .table td {
            white-space:nowrap;
        }
        .table th {
            white-space:nowrap;
        }
    </style>

</head>

<body>

<div class="container-fluid">
    <div class="row">
        {{--@if (count($accounts))--}}
        <div class="table-responsive-sm">
            <table class="table table-bordered table-sm" id="ArbetTable">
                {{--<thead>--}}
                {{--<tr>--}}
                    {{--@foreach($headers as $header)--}}
                        {{--<th> {{ $header }}</th>--}}
                    {{--@endforeach--}}
                {{--</tr>--}}
                {{--</thead>--}}
                {{--<tbody>--}}
                    {{--@foreach($accounts as $account)--}}
                        {{--<tr>--}}
                            {{--<td>{{ $account->department }}</td>--}}
                            {{--<td>{{ $account->sales }}</td>--}}
                            {{--<td>{{ $account->consignor }}</td>--}}
                            {{--<td>{{ $account->receive }}</td>--}}
                            {{--<td>{{ $account->profit }}</td>--}}

                        {{--</tr>--}}
                    {{--@endforeach--}}
                {{--</tbody>--}}
            </table>
            {{--{!! $accounts->render() !!}--}}
        </div>


    </div>
</div>
<script>
    $(function () {
        $(function () {
            //1.初始化Table
            var oTable = new TableInit();
            oTable.Init();
        });
    })

    var TableInit = function () {
        var oTableInit = new Object();
        //初始化Table
        oTableInit.Init = function () {
            $('#ArbetTable').bootstrapTable({
                url: '{{ route('account.index') }}',         //请求后台的URL（*）
                method: 'get',                      //请求方式（*）
                // toolbar: '#toolbar',                //工具按钮用哪个容器
                striped: true,                      //是否显示行间隔色
                cache: false,                       //是否使用缓存，默认为true，所以一般情况下需要设置一下这个属性（*）
                pagination: true,                   //是否显示分页（*）
                sortable: false,                     //是否启用排序
                sortOrder: "asc",                   //排序方式
                //queryParams: oTableInit.queryParams,//传递参数（*）
                sidePagination: "server",           //分页方式：client客户端分页，server服务端分页（*）
                pageNumber: 1,                       //初始化加载第一页，默认第一页
                pageSize: 10,                       //每页的记录行数（*）
                pageList: [10, 25, 50, 100],        //可供选择的每页的行数（*）
                search: true,                       //是否显示表格搜索，此搜索是客户端搜索，不会进服务端，所以，个人感觉意义不大
                contentType: "application/x-www-form-urlencoded",
                strictSearch: true,
                showColumns: true,                  //是否显示所有的列
                showRefresh: true,                  //是否显示刷新按钮
                minimumCountColumns: 2,             //最少允许的列数
                clickToSelect: true,                //是否启用点击选中行
                height: 700,                        //行高，如果没有设置height属性，表格自动根据记录条数觉得表格高度
                uniqueId: "no",                     //每一行的唯一标识，一般为主键列
                showToggle: true,                    //是否显示详细视图和列表视图的切换按钮
                cardView: false,                    //是否显示详细视图
                detailView: false,                   //是否显示父子表
                columns: [
                    {
                        field: 'department',
                        title: '揽货部门'
                    },
                    {
                        field: 'sales',
                        title: '揽货人'
                    },
                    {
                        field: 'consignor',
                        title: '客户名称'
                    },
                    {
                        field: 'receive',
                        title: '本位币不含税收入'
                    },
                    {
                        field: 'profit',
                        title: '本位币不含税利润'
                    }
                ],
            });

        };


        // //得到查询的参数
        // oTableInit.queryParams = function (params) {
        //     var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
        //         limit: params.limit,   //页面大小
        //         offset:params.offset
        //     };
        //     return temp;
        // };
        return oTableInit;
    };


    function operateFormatter(value, row, index) {//赋予的参数
        return [
            '<a class="btn active disabled" href="#">编辑</a>',
            '<a class="btn active" href="#">档案</a>',
            '<a class="btn btn-default" href="#">记录</a>',
            '<a class="btn active" href="#">准入</a>'
        ].join('');
    }
</script>
</body>
