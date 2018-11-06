<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Equipmentquestions;

/**
 * EquipmentquestionsSearch represents the model behind the search form of `backend\models\Equipmentquestions`.
 */
class EquipmentquestionsSearch extends Equipmentquestions
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['equipmentQuestionId', 'equipmentSubCategoryId'], 'integer'],
            [['equipmentQuestionTitle', 'status'], 'safe'],
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
        $query = Equipmentquestions::find();

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
            'equipmentQuestionId' => $this->equipmentQuestionId,
            'equipmentSubCategoryId' => $this->equipmentSubCategoryId,
        ]);

        $query->andFilterWhere(['like', 'equipmentQuestionTitle', $this->equipmentQuestionTitle])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
