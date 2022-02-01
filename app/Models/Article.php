<?php

namespace App\Models;

use Illuminate\Support\Str;

// Respurces:
// https://laravel.com/docs/8.x/eloquent

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use \Soundasleep\Html2Text;
use \Orbit\Concerns\Orbital;

class Article extends Model
{
    use Orbital;

    protected $fillable = ['title', 'link', 'author', 'published_at'];

    public $timestamps = false;

    protected static function booted()
    {
        static::retrieved(function ($user) {
            $user->fixPublishedAt();
            $user->plaintext = $user->textOnlyContent();
        });
    }

    public static function schema(Blueprint $table)
    {
        $table->string('title');
        $table->string('link');
        $table->string('author');
        $table->timestamp('published_at');
    }

    public function getKeyName()
    {
        return 'link';
    }
    
    public function getIncrementing()
    {
        return false;
    }

    public function textOnlyContent()
    {
        $htmlToTextOptions = array(
            'ignore_errors' => true,
            'drop_links' => true
        );

        $textOnly = Html2Text::convert($this->content, $htmlToTextOptions);
        $oneLine = str_replace(array("\r","\n")," ", $textOnly);
        $onlyOneSpaceAllowed = preg_replace('!\s+!', ' ', $oneLine);

        return $onlyOneSpaceAllowed;
    }

    private function fixPublishedAt()
    {
        $old = $this->published_at;
        $normalized = strtotime($old);
        $readable = gmdate("d.m.Y H:i", $normalized);
        $this->published_at = $readable;
    }

}


// Article::create([
//     'title' => 'Test',
//     'link' => 'test-link'
// ]);
