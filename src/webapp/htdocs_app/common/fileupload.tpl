{{assign var=h value="`$n`_h"}}
<input type="file" id="{{$n}}" name="{{$n}}_f" class="wide" />
<span class="caption">※高さ100pxに縮小して表示されます。</span><br/>
<img class="sumb" id="{{$n}}_img" src="{{$a->imgPath($a->$h)}}" />
<input class="img_delete" type="button" name="{{$n}}_delete" value="削除" />
<input id="{{$n}}_hidden" type="hidden" name="{{$n}}_h" value="{{$a->$h|escape}}" />
