<?php

namespace App\Models;

use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveView;
use App\Models\Traits\Accessors\SharingLinkAccessor;
use App\Models\Traits\Mutators\SharingLinkMutator;
use App\Models\Traits\Relations\SharingLinkRelation;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $link
 * @property string $identifier
 * @property string|null $user_name
 * @property string $shareable_type
 * @property int $shareable_id
 * @property int $is_active
 * @property float $number_of_views
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $shareable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereNumberOfViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereShareableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereShareableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SharingLink whereUserName($value)
 * @mixin \Eloquent
 */
class SharingLink  extends Model 
{
	protected $guarded =[
		'id'
	];
	use SharingLinkRelation,SharingLinkAccessor,SharingLinkMutator;
}
