@extends('layouts.default')

@section('content')
    @include('layouts._message')
    <div style="margin-top: 30px">
        <form class="form-horizontal" role="form" action="{{ route('shipMessage.show') }}" method="POST" >
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <label for="firstname" class="col-sm-2 control-label">提单号</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="mbl_no"
                           placeholder="请输入提单号">
                </div>
            </div>
                <label>
                    <input type="radio" name="type"  value="1" checked> 发送
                    <input style="margin-left: 20px" type="radio" name="type"  value="2"> 删除
                </label>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">查询导出</button>
                </div>
            </div>
        </form>

        @isset($xml_data)
            <div class="row">
            <div class="col-md-8" style="text-align: left">XML结果</div>
            <div class="col-md-2" style="text-align: right"><a href="{{ route('shipMessage.download',['file_path' => $file_path]) }}"><button>另存为</button></a></div>
            <textarea class="form-control col-md-10" rows="20">
                {{ $xml_data }}
            </textarea>
            </div>
        @endisset

    </div>
@stop