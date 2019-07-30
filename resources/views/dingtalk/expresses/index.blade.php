<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>快件查询</title>
    <!-- Fonts -->
    {{--<link rel="dns-prefetch" href="//fonts.gstatic.com">--}}
    {{--<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">--}}
    {{--<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">--}}
    {{--<!-- Css -->--}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap3-typeahead.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.14.1/dist/bootstrap-table.min.css">
    <!-- Js -->
    <script src="{{ mix('js/app.js') }}"></script>
    {{--<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>--}}
    <script src="{{ asset('js/bootstrap-datetimepicker.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datetimepicker.zh-CN.js') }}"></script>
    <script src="https://cdn.bootcss.com/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.14.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.14.1/dist/locale/bootstrap-table-zh-CN.min.js"></script>

    <style>
        body {
            background: white;
        }
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
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            @include('layouts._message')

            <form  action="{{ route('expresses.export') }}"  method="POST" accept-charset="UTF-8" class="mt-4">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="mb-2">
                    <div class='input-group input-group-sm mb-2'>
                        <div class="input-group-prepend">
                            <span class="input-group-text">业务日期从</span>
                        </div>
                        <input type='text' name="complete_begin" class="form-control date"  autocomplete="off"/>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                        </div>
                    </div>
                    <div class='input-group input-group-sm mb-2'>
                        <div class="input-group-prepend">
                            <span class="input-group-text">业务日期到</span>
                        </div>
                        <input type='text' name="complete_end" class="form-control date"  autocomplete="off"/>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                        </div>
                    </div>
                    <button type="button" id="submit" class="btn btn-sm btn-outline-secondary btn-default btn-block disabled">查询</button>
                </div>
                <button type="submit" id="export" class="btn btn-sm btn-outline-secondary btn-default btn-block disabled">导出excel</button>
            </form>

            <div class="table-responsive-sm">
                <table class="table table-bordered table-sm" id="ArbetTable">
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<script>
    $(function () {
        // $(".date").on('blur',function() {
        //     var format = $(this).val().replace(/^(\d{4})(\d{2})(\d{2})$/, '$1-$2-$3');
        //     $(this).attr("value", format);
        // });

        //日期选项
        $('.date').datetimepicker({
            format: 'yyyymmdd',
            autoclose: true,
            todayBtn: true,
            closeBtn: true,
            language: 'zh-CN',
            todayBtn: 'linked', //今日按钮
            maxView: 3,
            minView: 2,
            clearBtn:true,
            forceParse:false,
            endDate:new Date(),
        });

        let oTable = new TableInit();
        oTable.Init();

        // $('#submit').click(function () {
        //     $('#ArbetTable').bootstrapTable('refreshOptions',{pageNumber:1,pageSize:15});
        // });

        let oButtonInit = new ButtonInit();
        oButtonInit.Init();
    });

    var TableInit = function () {
        var oTableInit = new Object();
        //初始化Table
        oTableInit.Init = function () {
            $('#ArbetTable').bootstrapTable({
                url: '{{ route('expresses.show') }}',         //请求后台的URL（*）
                method: 'get',                      //请求方式（*）
                //toolbar: '#toolbar',                //工具按钮用哪个容器
                //striped: true,                      //是否显示行间隔色
                cache: false,                       //是否使用缓存，默认为true，所以一般情况下需要设置一下这个属性（*）
                pagination: true,                   //是否显示分页（*）
                sortable: false,                     //是否启用排序
                sortOrder: "asc",                   //排序方式
                queryParams: oTableInit.queryParams,//传递参数（*）
                sidePagination: "server",           //分页方式：client客户端分页，server服务端分页（*）
                pageNumber:1,                       //初始化加载第一页，默认第一页
                pageSize: 15,                       //每页的记录行数（*）
                pageList: [15, 50, 200, 500],        //可供选择的每页的行数（*）
                //search: true,                       //是否显示表格搜索，此搜索是客户端搜索，不会进服务端，所以，个人感觉意义不大
                strictSearch: true,
                showColumns: true,                  //是否显示所有的列
                //showRefresh: true,                  //是否显示刷新按钮
                minimumCountColumns: 2,             //最少允许的列数
                clickToSelect: true,                //是否启用点击选中行
                //height: 700,                        //行高，如果没有设置height属性，表格自动根据记录条数觉得表格高度
                //uniqueId: "ID",                     //每一行的唯一标识，一般为主键列
                //showToggle:true,                    //是否显示详细视图和列表视图的切换按钮
                cardView: false,                    //是否显示详细视图
                detailView: false,                   //是否显示父子表
                // fixedColumns: true,                  //是否固定列
                // fixedNumber: 1,                     //固定列数
                //showFooter:true,                        //显示底部汇总栏
                columns: [
                    {
                        field: 'business_date',
                        title: '业务日期',
                    },
                    {
                        field: 'shipper_account_no',
                        title: '发货人账号',
                    },
                    {
                        field: 'mbl_no',
                        title: '运单号',
                    },
                    {
                        field: 'no_of_package',
                        title: '件数'
                    },
                    {
                        field: 'charging_weight',
                        title: '计费重量',
                    },
                    {
                        field: 'cargo_type',
                        title: '种类'
                    },
                    {
                        field: 'consignee_country_name',
                        title: '收件国家'
                    },
                    {
                        field: 'sales_name',
                        title: '揽货人'
                    },
                    {
                        field: 'payment_mode',
                        title: '付款方式'
                    },
                    {
                        field: 'settle_cust_name',
                        title: '结算对象'
                    }
                ],
            });

        };

        //得到查询的参数
        oTableInit.queryParams = function (params) {
            var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
                limit: params.limit,   //页面大小
                page:(params.offset/params.limit)+1,  //页码
                complete_begin: $("input[name='complete_begin']").val(),
                complete_end: $("input[name='complete_end']").val(),
            };
            return temp;
        };
        return oTableInit;
    };

    var ButtonInit = function () {
        var oInit = new Object();
        var postdata = {};

        oInit.Init = function () {
            $("#submit").click(function () {
                if(phoneOrPc()){
                    $("#export").show();
                }
                $("#ArbetTable").bootstrapTable('refresh',{
                    pageNumber: 1,
                });
            });
        };
        return oInit;
    };


    function phoneOrPc(){
        var sUserAgent = navigator.userAgent.toLowerCase();
        var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
        var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
        var bIsMidp = sUserAgent.match(/midp/i) == "midp";
        var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
        var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
        var bIsAndroid = sUserAgent.match(/android/i) == "android";
        var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
        var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
        if (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) {
            return false;
        } else {
            return true;
        }
    }
</script>
