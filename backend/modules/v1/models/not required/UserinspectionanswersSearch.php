<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Userinspectionanswers;

/**
 * UserinspectionanswersSearch represents the model behind the search form of `backend\models\Userinspectionanswers`.
 */
class UserinspectionanswersSearch extends Userinspectionanswers
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inspectionAnswerId', 'inspectionSubCategoryId', 'inspectionAnswerQuestionId'], 'integer'],
            [['inspectionAnswer', 'createdOn', 'modifiedOn'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Userinspectionanswers::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'inspectionAnswerId' => $this->inspectionAnswerId,
            'inspectionSubCategoryId' => $this->inspectionSubCategoryId,
            'inspectionAnswerQuestionId' => $this->inspectionAnswerQuestionId,
            'createdOn' => $this->createdOn,
            'modifiedOn' => $this->modifiedOn,
        ]);

        $query->andFilterWhere(['like', 'inspectionAnswer', $this->inspectionAnswer]);

        return $dataProvider;
    }
}
