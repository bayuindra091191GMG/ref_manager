<?php

namespace App\Transformer;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class AttendanceTransformer extends TransformerAbstract
{
    public function transform(Attendance $data){
        $date = Carbon::parse($data->created_at)->toIso8601String();
        $type = 'Karyawan';
        if($data->partner_number != null && $data->partner_number != ''){
            $type = 'Therapis';
        }

        $code = '-';
        if($data->employee_code != null && $data->employee_code != ''){
            $code = $data->employee_code;
        }
        else if($data->partner_number != null && $data->partner_number != ''){
            $code = $data->partner_number;
        }

        $name = '-';
        if($data->employee_name != null && $data->employee_name != ''){
            $name = $data->employee_name;
        }
        else if($data->partner_name != null && $data->partner_name != ''){
            $name = $data->partner_name;
        }

        $img = '-';
        if($data->img_path != null && $data->img_path != ''){
            $img = '<img src="' . Storage::disk('attendance_img')->url($data->img_path) .'" width="100"/>';
        }

        return[
            'created_at'        => $date,
            'type'              => $type,
            'id'                => $data->id,
            'code'              => $code,
            'name'              => $name,
            'date'              => $date,
            'img_path'          => $img,
            'in_time'           => $data->in_time == null ? '-' : $data->in_time->format('H:i'),
            'out_time'          => $data->out_time == null ? '-' : $data->out_time->format('H:i'),
        ];
    }
}
