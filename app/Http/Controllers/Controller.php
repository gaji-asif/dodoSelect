<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponse;

    // public function userType()
    // {
    //     if(Auth::user()->role == 'member')
    //     {
    //         $id = Auth::user()->id;
    //     }
    // }


    protected function ksherAppId()
    {
        return 'mch37567';
    }


    protected function ksherPrivateKey()
    {
        $privatekey = <<<EOD
    -----BEGIN RSA PRIVATE KEY-----
    MIICYgIBAAKBgQCPzwGZv5sCMwf8Sv+FXUqrULSEdeB846z2OCnPw+ynDTUqApRz
    0Goj1gYaK5Gu4vLxTH06PpL96sAB9C0pACBz3xewotdAwoHK0B86TaWk0bt4+jSL
    HMAvgLOF2DH5uAlDzYp8KtQAyhXOowds/20POw+Q3m2RgLCMXQ4OzElp8QIDAQAB
    AoGBAI4VecBdZhp7LwWfV+x9axvuRhyllmHuVOKERRNIwZWfYAqct+3hWi0D9c1/
    hJWlF2E/MG8Oig6kFIcZp5OwAvIHsEkJjryQSk4qERpuU99TG9u5ayGmFUPaC0x6
    fzgEw3+ANYOytWTfsxGbUL1SFoZ1yqKD/iKuBE2BXgM6fZbBAkUAv3jyTVA5R+kg
    B3eFSu+hywi87Q2zZ+myBHGBC4Zb3mhmKRoiBMGZS40y9JXNsmrx3IhynQDSiywJ
    7DyX+Bo7SJ90eykCPQDAReuuYuU/wqcqtnscRzVCW9aydquaDYUHOUXWsAGdghtK
    SJFJW717RLHO/3L230f2pl5TBfPG3hGYmYkCRA8O0e9mmbqgCNbNfXwRMGYpP8Jc
    y3kmlctnqcBgRqVNDIu69GXvW8DnT9SQW2bmpjKzwF+8itJLGlSrxz/JwFPLxntR
    Aj0AjT1PqaSAHtxQjDHMMbOlTf/EsQg3ekzgIbRStyhHp3qBrYmtICRCBqEptJM1
    0l+mr2r68yX2M2nBp0VxAkUAkT6IL2UAbBi5mTK2YgakqyWCcFsLg7fGtArKcNiF
    QssbrooyyUHq8GKQ/4IYQO6M80xTf6vY3r3Gxs8LkqoQirHwRN0=
    -----END RSA PRIVATE KEY-----
    EOD;

        return $privatekey;
    }
}
