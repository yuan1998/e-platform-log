<style>
    .dashboard-title .links {
        text-align: center;
        margin-bottom: 2.5rem;
    }
    .dashboard-title .links > a {
        padding: 0 25px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
        color: #fff;
    }
    .dashboard-title h1 {
        font-weight: 200;
        font-size: 2.5rem;
    }
    .dashboard-title .avatar {
        background: #fff;
        border: 2px solid #fff;
        width: 70px;
        height: 70px;
    }
</style>

<div class="dashboard-title card bg-primary">
    <div class="card-body">
        <div class="text-center ">
            <img class="avatar img-circle shadow mt-1" src="{{ $avatar }}">

            <div class="text-center mb-1">
                <h3 class="mb-3 mt-2 text-white">{{$greeting}}好, {{$username}} </h3>
                <h5 class="text-white " id="poem_sentence">
                    生活变的再糟糕，也不妨碍我变得更好
                </h5>
                <h6 class="text-white text-right" id="poem_info">
                </h6>
            </div>
        </div>
    </div>
</div>

<script src="https://sdk.jinrishici.com/v2/browser/jinrishici.js" charset="utf-8"></script>
<script type="text/javascript">
    jinrishici.load(function(result) {
        // 自己的处理逻辑
        $('#poem_sentence').text(result.data.content);
        $('#poem_info').text( '一 【' + result.data.origin.dynasty + '】' + result.data.origin.author + '《' + result.data.origin.title + '》');
        console.log(result)
    });
</script>
