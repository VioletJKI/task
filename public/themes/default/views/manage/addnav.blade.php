
<h3 class="header smaller lighter blue mg-bottom20 mg-top12">添加自定义导航</h3>

   {{-- <div class="widget-header widget-header-flat">
        <h5 class="widget-title">添加自定义导航</h5>
    </div>--}}


<div class="g-backrealdetails clearfix bor-border">
        <form action="/manage/addNav" method="post">
            {{ csrf_field() }}
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">标题：</p>
                <p class="col-md-11 text-left">
                   <input type="text" name="title" id="title" value="">
                    {{ $errors->first('title') }}
                </p>
            </div>
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">链接：</p>
                <p class="col-md-11 text-left">
                    <input type="text" name="link_url" id="link_url" value="">
                    {{ $errors->first('link_url') }}
                </p>
            </div>
            {{--<tr>
                <td class="text-right">样式：</td>
                <td class="text-left">
                    <input type="text" name="style" id="style" value="">
                    {{ $errors->first('style') }}
                </td>
            </tr>--}}
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">排序：</p>
                <p class="col-md-11 text-left">
                    <input type="text" name="sort" id="sort" value="">
                    {{--{{ $errors->first('sort') }}--}}
                </p>
            </tr>
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">新窗口打开：</p>
                <p class="col-md-11 text-left">
                    <label class="">
                        <input type="radio"  name="is_new_window" value="1" checked/>
                        <span class="lbl"></span>是
                        <input type="radio" name="is_new_window" value="2"/>
                        <span class="lbl"></span>否
                    </label>
                    {{--{{ $errors->first('is_new_window') }}--}}
                </p>
            </div>
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">显示模式：</p>
                <p class="col-md-11 text-left">
                    <label class="">
                        <input type="radio"  name="is_show" value="1" checked/>
                        <span class="lbl"></span>显示
                        <input type="radio" name="is_show" value="2"/>
                        <span class="lbl"></span>隐藏
                    </label>
                    {{--{{ $errors->first('is_show') }}--}}
                </p>
            </div>

            {{--<div class="bankAuth-bottom clearfix col-xs-12">
                <p class="text-right"></p>
                <p class="text-left">
                    <button class="btn btn-primary btn-sm" type="submit">提交</button>
                </p>
            </div>--}}
        </div>
            <div class="col-md-12">
                <div class="clearfix row bg-backf5 padding20 mg-margin12">
                    <div class="col-xs-12">
                        <div class="col-md-1 text-right"></div>
                        <div class="col-md-10">

                            <button form="success-case" class="btn btn-primary btn-sm" type="submit">提交</button>
                            <a href="" title="" class=" add-case-concel">返回</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}
<!-- basic scripts -->
