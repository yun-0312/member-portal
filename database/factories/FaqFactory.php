<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 💬 保険診療・レセプト請求・査定返戻に関するFAQ（Q&Aペア）
        $faqList = [
            [
                'question' => '同日に同一診療科で午前・午後に分けて再診を行った場合、再診料は2回算定できますか？',
                'answer'   => "原則として同一日・同一診療科における再診料は【1日につき1回】のみの算定となります。\n\nただし、午前と午後でそれぞれ異なる傷病（急性増悪等）に対して緊急で診療を行った場合など、特別な事情がある場合は「2回目の再診料（同日再診）」として所定点数の100分の50を算定可能です。\n\n※レセプトの摘要欄に「同日再診を行った医学的必要性および時間」を明記してください。",
            ],
            [
                'question' => '特定疾患療養管理料を算定する際、主病の判断基準やレセプト摘要欄の記載要件について教えてください。',
                'answer'   => "特定疾患療養管理料は、生活習慣病等の対象疾患（高血圧症、脂質異常症、糖尿病等）が「主病」として治療継続されている場合に月2回に限り算定可能です。\n\n【留意事項】\n1. 主病が複数ある場合でも算定は月2回までです。\n2. 初診料を算定した月は、初診日から1か月を経過した日以降でなければ算定できません。\n3. 主病名および指導内容の要点をカルテに記載し、レセプトにも主病名を主病欄に正しく記載してください。\n\n詳細な通知事項・FAQは社会保険診療報酬支払基金のHPをご参照ください。\nhttps://example.com/receipt/faq/guideline-specific-disease-management-check-long-url-path",
            ],
            [
                'question' => '処方箋料の算定において、7種類以上の内服薬を投薬した場合の減算規定と例外規定は？',
                'answer'   => "1回の処方において内服薬が【7種類以上】となる場合、処方箋料は所定点数（42点）から「28点」に減算となります。\n\n【例外（減算対象外）となる場合】\n・臨時の投与（投与期間が2週間以内のもの）である場合\n・処方内容の変更により一時的に7種類以上となった場合\n・「多剤投与に係る症状詳記」をレセプト摘要欄に添付・記載し、専門の医師が投与の継続性を判断している場合\n\n※慢性疾患等で長期処方を行う際は投与日数の上限等にもご注意ください。",
            ],
            [
                'question' => 'レセプトが「症状詳記不足」で返戻されました。再請求時の具体的な対応方法は？',
                'answer'   => "医学的必要性や指導内容の確認が必要として査定・返戻された場合、以下の手順で再請求を行います。\n\n1. 返戻レセプトの「返戻理由欄」を確認する\n2. 傷病名と診療行為の整合性、算定開始日、投与期間等の点検を行う\n3. 摘要欄に「医学的必要性、画像所見、既往歴、これまでの経過」等の不服申し立て（症状詳記）を具体的に追記する\n4. 再審査請求書とともに当月分レセプトと併せて再提出する\n\n▼返戻・査定事例集とコメント記載例\nhttps://example.com/medical-claims/rejection-cases/comment-examples-detail-check-page",
            ],
            [
                'question' => 'オンライン資格確認（マイナ保険証）で資格照会ができない場合の保険請求はどうすればよいですか？',
                'answer'   => "端末の障害や一時的な資格未登録等でオンライン照会がエラーとなった場合は、以下の順でご対応ください。\n\n・「被保険者資格申告書」を患者様に記入いただく\n・マイナポータル画面（スマートフォン）の資格情報画面を提示いただく\n・従来の健康保険証をお持ちの場合は、その記載内容をもとに手入力で登録する\n\nレセプト請求時は「不祥事・システム障害時の暫定請求コード（注記）」を付与して提出してください。",
            ],
            [
                'question' => '公費負担医療（難病医療・小児慢性・生活保護等）と健康保険の併用時のレセプト記載順序は？',
                'answer'   => "公費負担医療と法保（健康保険）を併用する場合、レセプトの公費受給者番号欄には「優先順位に従って第一公費・第二公費」を入力します。\n\n【主な優先順序】\n1. 感染症法（結核等）や指定難病等の国庫負担公費（第1優先）\n2. 地方自治体の独自公費（乳幼児医療・ひとり親等）（第2優先）\n\n※患者上限額管理票の記載漏れによる一部負担金の過不足が生じないよう、窓口会計時の確認を徹底してください。",
            ],
        ];

        // ランダムにQ&Aセットを抽出
        $selectedFaq = $this->faker->randomElement($faqList);
        $randomDate  = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'received_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'question'    => $selectedFaq['question'],
            'answer'      => $selectedFaq['answer'],
            'category_id' => FaqCategory::inRandomOrder()->value('id') ?? 1,
            'created_by'  => User::whereHas('role', fn($q) => $q->where('name', 'staff'))
                ->inRandomOrder()
                ->value('id') ?? 1,
            'created_at'  => $randomDate,
            'updated_at'  => $randomDate,
        ];
    }
}