<?php

namespace Database\Factories;

use App\Models\Video;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 研修タイトルと概要・タイムスケジュール等のセット
        $videoDataList = [
            [
                'title' => '令和8年度 第1回 地域医療連携研修会「退院支援と多職種協働」',
                'description' => "【概要】\n2026年6月15日に開催された「地域医療連携研修会」のアーカイブ動画です。\n病院から在宅へのスムーズな移行を目指した退院支援の手順と、多職種（医師・看護師・MSW・ケアマネジャー）による情報共有のポイントについて解説しています。\n\n【プログラム】\n00:00 主旨説明・開会挨拶\n05:20 講演「地域包括ケアシステムにおける退院調整の実際」\n45:00 事例検討（入退院時サマリーの活用）\n01:15:00 質疑応答・まとめ\n\n【配布資料】\n下記URLより資料データをダウンロードしてご覧ください。\nhttps://example.com/share/videos/materials/2026/regional-medical-care-workshop-document-download-test-long-url-path",
            ],
            [
                'title' => '在宅医療・訪問看護実践研修プログラム（全2回・前編）',
                'description' => "【概要】\n在宅医療に携わる医療従事者・介護スタッフを対象とした実践的な研修セミナーです。\n前編となる本動画では、訪問診療における急変時対応と、オンライン診療システムを活用した最新の運用事例について紹介します。\n\n【講師】\n〇〇総合病院 訪問診療科 部長 山田 太郎 氏\n\n【受講上の注意】\n※本動画の無断転載・再配布は固く禁じます。\n※研修後のアンケート提出（下記リンク）をもって受講修了となります。\nhttps://example.com/enquete/2026/zaitaku-medical-seminar-part1-response-form-check",
            ],
            [
                'title' => '医療機関における最新の感染症対策講習会（2026年改定版）',
                'description' => "【概要】\n院内感染防止に向けた基礎知識と標準予防策（スタンダード・プリコーション）の再確認を目的とした講習会です。\n個人防護具（PPE）の正しい着脱手順や、環境表面の消毒基準など、現場で直ちに実践できる最新ガイドラインをまとめています。\n\n【タイムライン】\n・00:00 感染対策の基礎講義\n・20:30 PPE着脱の標準手順（実演動画あり）\n・50:10 質疑応答と症例共有\n\n※資料閲覧パスワードは事務局からの案内メールをご確認ください。",
            ],
            [
                'title' => '災害時医療対応研修会「BCP（事業継続計画）の策定と運用」',
                'description' => "【概要】\n大規模災害発生時における医療機関および介護事業所の初動対応と、BCP運用の実効性を高めるポイントを学ぶ研修会です。\n非常用電源・通信手段の確保、職員の安否確認体制の整備について具体的な事例を交えて解説しています。\n\n【共催】\n災害医療対策委員会 / 地域の医療を守る会\n\n【関連資料リンク】\nhttps://example.com/docs/disaster-prevention-bcp-guideline-2026-detail-check-page-test-long-url-path",
            ],
            [
                'title' => '高齢者医療と介護連携セミナー「認知症ケアと意思決定支援」',
                'description' => "【概要】\n高齢者医療の現場で求められる「人生の最終段階における医療・ケアの決定プロセス（ACP）」をテーマにした研修動画です。\nご本人・ご家族との対話の進め方や、医療・介護チーム全体での合意形成について深掘りします。\n\n【対象】\n医師、看護師、社会福祉士、ケアマネジャー、施設管理者等",
            ],
        ];

        $urls = [
            'https://www.youtube.com/watch?v=Ke6XX8FHOHM',
            'https://www.youtube.com/watch?v=zsJpUCWfyPE',
            'https://www.youtube.com/watch?v=d0yGdNEWdn0',
            'https://www.youtube.com/watch?v=w-HYZv6HzAs',
            'https://www.youtube.com/watch?v=8S0FDjFBj8o',
        ];

        // ランダムに研修セットを選択
        $selectedData = $this->faker->randomElement($videoDataList);

        $publishedAt = now()->subDays(fake()->numberBetween(1, 30));
        $expiredAt   = $publishedAt->copy()->addDays(fake()->numberBetween(30, 120));

        return [
            'title' => $selectedData['title'],
            'description' => $selectedData['description'],
            'external_url' => $this->faker->randomElement($urls),
            'published_at' => $publishedAt,
            'expired_at' => $expiredAt,
            'created_by' => User::whereHas('role', fn($q) => $q->where('name', 'staff'))
                ->inRandomOrder()
                ->value('id') ?? 1,
        ];
    }
}