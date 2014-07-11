<?php

class ICVotePlugManager extends ICPlugManager
{
    public static function getArticleVote()
    {
        return new ICArticleVote();
    }
}
