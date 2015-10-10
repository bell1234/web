<?php
class Analytics {
    /**************
     * Internal Analytics Class Functions and Var's
     **************/

    //keeps track of segments initilization status
    private static $inited = false;

    //initialize Segment, must call this before we can track anything (only need to call once)
    private static function init() {
        Segment::init("kEGvVw6uhrd6l8WXPJaVeIzMlCqBG24O"); //<-- secret key
        self::$inited=true;
    }

    //function to handle the overhead of tracking (don't track unless in prod, make sure
    // we are initialized, etc)
    private static function track($data) {
        //if this isn't prod, don't do anything (don't want to submit fake data)
        if(!Yii::app()->params['isProd']) {
            return;
        }

        //init segment
        if(!self::$inited) {
            self::init();
        }

        try {
            Segment::track($data);
        }
        catch (Exception $e) {}
    }



    /**************
     * SiteController Analytics
     **************/


    /**
     * @param Questions $questionModel The question that was posted
     */
    public static function newUserSignup($user) {
        try {
            self::track(array(
                "userId" => $user->id,
                "event" => "New User Signup",
                "properties" => array(
			"username" => $user->username,
			"activkey" => $user->activkey,
			"create_time" => $user->create_time,
                )
            ));
        }
        catch (Exception $e) {
            self::analyticError("paidQuestionPosted");
        }
    }


    /**************
     * PostsController Analytics
     **************/


    /**
     * @param Questions $questionModel The question that was posted
     */
    public static function paidQuestionPosted($questionModel) {
        try {
            self::track(array(
                "userId" => $questionModel->owner_id,
                "event" => "Posted a Paid Question",
                "properties" => array(
                    "tutors_invited" => count($questionModel->getInvitedTutorNames()),
                    "time_limit" => $questionModel->time_limit,
                    "tier" => (int)$questionModel->tier,
                    "urgent" => (bool)$questionModel->urgent,
                    "private" => (bool)$questionModel->private,
                    "category" => $questionModel->category->name,
                )
            ));
        }
        catch (Exception $e) {
            self::analyticError("paidQuestionPosted");
        }
    }


    /**********
     * Internal Bug tracking Analytics
     **********/
    /**
     * @param string $errorCode the error code (404, 400, 500 ...)
     */
    public static function didEncounterErrorPage($errorCode, $errorMessage) {
        try {
            self::track(array(
                "userId" => Yii::app()->user->id,
                "event" => "Website User Encountered an Error Page",
                "properties" => array (
                    "error_code" => $errorCode,
                    "message" => $errorMessage
                )
            ));
        }
        catch (Exception $e) {
            self::analyticError('didEncounterErrorPage');
        }
    }

    /***********
     * catch (Exception $e)ing failed functions
     **********/

    /**
     * @param string $failedFunctionName the name of the failed function
     */
    private static function analyticError($failedFunctionName) {
        self::track(array(
            "userId" => Yii::app()->user->id,
            "event" => "Analytics Function Has Failed",
            "properties" => array (
                "function_name" => $failedFunctionName
            )
        ));
    }
}