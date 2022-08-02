<?php

namespace Satis2020\ServicePackage\Rules;

use Illuminate\Contracts\Validation\Rule;
use Satis2020\ServicePackage\Models\Message;

class MessageBelongsToDiscussionRules implements Rule
{

    protected $discussion;

    public function __construct($discussion)
    {
        $this->discussion = $discussion;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public function passes($attribute, $value)
    {

        if (is_null($value)) {
            return true;
        }

        $message = Message::findOrFail($value);

        return $message->discussion_id == $this->discussion->id;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'This message does not belongs to the discussion';
    }

}
