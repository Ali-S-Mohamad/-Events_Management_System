<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
        protected $fillable = ['url','imageable_id','imageable_type', 'is_cover'];

        /**
         * Relation of imageable
         * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, Image>
         */
        public function imageable() {
            return $this->morphTo();
        }
}


