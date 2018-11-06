<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\InspectionRemarks;

/**
 * InspectionRemarksSearch represents the model behind the search form of `backend\models\InspectionRemarks`.
 */
class InspectionRemarksSearch extends InspectionRemarks
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Id', 'inspectionId', 'equipmentQuestionId'], 'integer'],
            [['remark'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = InspectionRemarks::find();

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
            'Id' => $this->Id,
            'inspectionId' => $this->inspectionId,
            'equipmentQuestionId' => $this->equipmentQuestionId,
        ]);

        $query->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}
