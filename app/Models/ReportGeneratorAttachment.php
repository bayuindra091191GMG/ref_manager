<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReportGeneratorAttachment
 * 
 * @property int $id
 * @property int|null $header_id
 * @property string|null $file_name
 * 
 * @property ReportGenerator|null $report_generator
 *
 * @package App\Models
 */
class ReportGeneratorAttachment extends Model
{
	protected $table = 'report_generator_attachments';
	public $timestamps = false;

	protected $casts = [
		'header_id' => 'int'
	];

	protected $fillable = [
		'header_id',
		'file_name'
	];

	public function report_generator()
	{
		return $this->belongsTo(ReportGenerator::class, 'header_id');
	}
}
