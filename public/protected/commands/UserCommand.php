<?php

class UserCommand extends CConsoleCommand
{
    const RATING_USERS_PER_ITARATION = 500;

    public function actionRating()
    {
        Yii::import('application.model.User', true);
        $letters='abcdefghijklmnopqrstuvwxyz';
        $results=array();

        for($i=0; $i<strlen($letters); $i++){
            $letter = $letters[$i];
            echo "Working on '$letter' letter...\n";

            $dataProvider = new CActiveDataProvider('User', array(
                'criteria' => array(
                    'select' => 't.rating',
                    'order' => 't.username',
                    'condotion' => 't.username LIKE :username',
                    'params' => array(':username' => $letter.'%')
                )
            ));

            $iterator = new CDataProviderIterator($dataProvider,
                self::RATING_USERS_PER_ITARATION);

            $result = 0;
            foreach ($iterator as $user)
                $result += $user->rating/($iterator->totalItemCount) * 100000;

            $results[$letter] = $result / 100000;
            echo "Letter '$letter' is done!\n";
            
        }
        file_put_contents(Yii::getPathOfAlias('application.data') . '/userRating.php',
            "<?php\n\nreturn " . var_export($results, true). ";\n", LOCK_EX);
    }
}
