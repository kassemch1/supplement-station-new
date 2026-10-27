<?php

use App\Models\Category;
use App\Services\PageCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// categories.description: the "About" text under a category's products (search engines read it) and the
// page's meta description (its first sentence or two). Every category gets a starter text the admin can
// change in Manage Categories; a category whose text is already filled in is left alone. Paragraphs are
// separated by an empty line. down() only removes the column.
return new class extends Migration
{
    private const TEXTS = [
        // ---------- main categories ----------
        'protein' => "Shop protein powder in Lebanon at Supplement Station, Beirut: whey, whey isolate, casein, beef and vegan protein from Optimum Nutrition, MuscleTech, BPI and other trusted brands. Every product is original, priced in USD, and delivered across Lebanon.\n\nProtein powder is the easiest way to reach your daily protein target for building muscle and recovering from training. Whey is the all-rounder for after your workout, isolate suits people who want fewer carbs and less lactose, casein digests slowly and is popular before bed, and beef and vegan proteins are dairy-free options.\n\nNot sure which one fits you? Message us on WhatsApp and we will help you choose.",
        'creatine' => "Buy creatine in Lebanon at Supplement Station, Beirut: creatine monohydrate and creatine blends from trusted brands, all original, priced in USD and delivered across Lebanon.\n\nCreatine is one of the most researched supplements for strength and power in the gym. Most people take 3 to 5 grams every day, on training and rest days, mixed with water, juice or their protein shake. Creatine monohydrate is the simple, proven choice; blends add extra ingredients for those who want more in one scoop.",
        'mass-gainers' => "Shop mass gainers in Lebanon at Supplement Station, Beirut: weight gainers from trusted brands, original and delivered across Lebanon.\n\nA mass gainer mixes protein with carbohydrates, so one shake adds a large number of calories to your day. It suits people who find it hard to eat enough to gain weight, and those with a fast metabolism. Take it between meals or after training, and adjust the serving to how fast you want to gain.",
        'amino-acids' => "Shop amino acids in Lebanon at Supplement Station, Beirut: EAA and BCAA, glutamine, and citrulline and arginine from trusted brands, all original and delivered across Lebanon.\n\nAmino acids are the building blocks of protein. EAA and BCAA drinks are popular during long or fasted workouts, glutamine after hard training, and citrulline and arginine before training for a better pump.",
        'testosterone-boosters' => "Shop testosterone boosters in Lebanon at Supplement Station, Beirut: herbal and vitamin formulas for men who train, from trusted brands, original and delivered across Lebanon.\n\nThese formulas usually combine herbs such as tribulus, fenugreek or ashwagandha with zinc, magnesium and vitamin D. Read each product's label for its ingredients and serving, and ask your doctor first if you have a medical condition or take medication.",
        'pre-workout' => "Shop pre-workout supplements in Lebanon at Supplement Station, Beirut: stimulant pre-workouts, pump and stimulant-free formulas, caffeine and beta-alanine from trusted brands, all original and delivered across Lebanon.\n\nA pre-workout gives you energy and focus for training; take it 20 to 30 minutes before you start. Training in the evening or sensitive to caffeine? Choose a stimulant-free pump formula. New to pre-workouts? Start with half a scoop to see how you feel.",
        'fat-burner' => "Shop fat burners in Lebanon at Supplement Station, Beirut: thermogenic formulas, L-carnitine and CLA from trusted brands, original and delivered across Lebanon.\n\nFat burners support a diet and training plan aimed at losing fat; they work best together with a calorie deficit, enough protein and regular exercise. Many thermogenics contain caffeine, so check the label if you also take a pre-workout or drink a lot of coffee.",
        'health-wellness' => "Shop vitamins and health supplements in Lebanon at Supplement Station, Beirut: multivitamins, minerals, omega 3, collagen, joint support and herbs from trusted brands, all original and delivered across Lebanon.\n\nThese are everyday supplements for people who train and people who don't. A multivitamin and omega 3 are a good base for most people, while collagen and joint support are popular with those who train hard or often.",
        'snacks-carbs' => "Shop protein bars and carb supplements in Lebanon at Supplement Station, Beirut: high-protein snacks and carbohydrate powders from trusted brands, original and delivered across Lebanon.\n\nProtein bars are an easy snack on the go or between meals. Carb powders give you energy during long training sessions and help you refuel afterwards, alone or mixed with your protein shake.",
        'accessories' => "Shop gym accessories in Lebanon at Supplement Station, Beirut: shakers, gym gear and training shirts.\n\nAdd them to your supplement order and get everything delivered together, anywhere in Lebanon.",
        'offers' => "Supplement deals in Lebanon: the current offers and discounted products at Supplement Station, Beirut. Every product is original, from the same trusted brands as the rest of the shop, and delivered across Lebanon.\n\nOffers change often and stock is limited, so check back regularly or subscribe to hear about new deals first.",

        // ---------- protein ----------
        'whey-protein' => "Buy whey protein in Lebanon at Supplement Station, Beirut: Optimum Nutrition Gold Standard, MuscleTech, BPI and other trusted brands, all original, priced in USD and delivered across Lebanon.\n\nWhey is the most popular protein powder: it digests quickly, mixes easily and tastes good with water or milk. One scoop after training, or between meals, helps you reach your daily protein target for building muscle and recovering. Choose a whey blend for the best value, or a whey isolate if you want fewer carbs and less lactose.",
        'whey-protein-isolate' => "Buy whey protein isolate in Lebanon at Supplement Station, Beirut: isolate powders from trusted brands, all original and delivered across Lebanon.\n\nWhey isolate is filtered further than regular whey, so each scoop has more protein with less fat, fewer carbs and very little lactose. It suits people on a diet, people sensitive to lactose, and anyone who wants the cleanest protein after training.",
        'casein' => "Buy casein protein in Lebanon at Supplement Station, Beirut: original casein powders from trusted brands, delivered across Lebanon.\n\nCasein digests slowly and releases protein over several hours, which is why many people take it before bed or when they will go a long time without eating. It mixes thicker than whey and works well as a protein pudding.",
        'beef-protein' => "Buy beef protein in Lebanon at Supplement Station, Beirut: original beef protein isolate powders from trusted brands, delivered across Lebanon.\n\nBeef protein isolate is a dairy-free and lactose-free alternative to whey, made from beef. It suits people who don't tolerate milk well but still want a high-protein shake after training.",
        'vegan-protein' => "Buy vegan protein in Lebanon at Supplement Station, Beirut: plant-based protein powders from trusted brands, original and delivered across Lebanon.\n\nVegan protein blends pea, rice and other plant proteins into a complete protein with no dairy at all. It suits vegans, vegetarians and anyone avoiding lactose who wants a protein shake after training or between meals.",

        // ---------- creatine ----------
        'creatine-monohydrate' => "Buy creatine monohydrate in Lebanon at Supplement Station, Beirut: pure, original creatine from trusted brands, priced in USD and delivered across Lebanon.\n\nCreatine monohydrate is the most studied form of creatine and the simple, proven choice for strength and power. Take 3 to 5 grams every day, on training and rest days, with water, juice or your protein shake. It has no taste, so it mixes into anything.",
        'creatine-blends' => "Buy creatine blends in Lebanon at Supplement Station, Beirut: original creatine formulas from trusted brands, delivered across Lebanon.\n\nCreatine blends combine creatine with other ingredients, such as other forms of creatine, carbohydrates or amino acids, in one scoop. Check each product's label for what it contains and the serving it recommends.",

        // ---------- amino acids ----------
        'eaa-bcaa' => "Buy EAA and BCAA in Lebanon at Supplement Station, Beirut: amino acid powders from trusted brands, all original and delivered across Lebanon.\n\nEAA contain all nine essential amino acids; BCAA contain the three branched-chain ones (leucine, isoleucine and valine). Both are popular as a flavoured drink to sip during training, especially long or fasted workouts.",
        'glutamine' => "Buy L-glutamine in Lebanon at Supplement Station, Beirut: original glutamine powders from trusted brands, delivered across Lebanon.\n\nGlutamine is one of the most common amino acids in the body. People who train hard often take it after their workout or before bed, mixed with water or their protein shake; it has almost no taste.",
        'citrulline-arginine' => "Buy citrulline and arginine in Lebanon at Supplement Station, Beirut: original products from trusted brands, delivered across Lebanon.\n\nL-citrulline and L-arginine are amino acids popular before training for a better pump. They are the main ingredients of many stimulant-free pre-workouts, and many people add them to their regular pre-workout.",

        // ---------- pre-workout ----------
        'stimulant-pre-workout' => "Buy stimulant pre-workouts in Lebanon at Supplement Station, Beirut: high-energy pre-workout formulas from trusted brands, original and delivered across Lebanon.\n\nThese formulas use caffeine and other stimulants for energy and focus, usually with beta-alanine and citrulline for endurance and pump. Take one 20 to 30 minutes before training, avoid it late in the day, and start with half a scoop if you are new to pre-workouts.",
        'pump-stim-free' => "Buy stimulant-free and pump pre-workouts in Lebanon at Supplement Station, Beirut: caffeine-free formulas from trusted brands, original and delivered across Lebanon.\n\nPump formulas focus on citrulline, nitrates and other ingredients for blood flow and a fuller look while you train, without caffeine. They are a good choice for evening workouts, people sensitive to caffeine, or to stack with a regular pre-workout.",
        'caffeine' => "Buy caffeine supplements in Lebanon at Supplement Station, Beirut: original caffeine products from trusted brands, delivered across Lebanon.\n\nCaffeine is the most common energy ingredient in pre-workouts and fat burners. Taking it on its own lets you control exactly how much you take. Count the caffeine from coffee and other supplements too, and avoid it in the hours before sleep.",
        'beta-alanine' => "Buy beta-alanine in Lebanon at Supplement Station, Beirut: original beta-alanine from trusted brands, delivered across Lebanon.\n\nBeta-alanine is a common pre-workout ingredient used for endurance in high-repetition sets and intense training. It can cause a harmless tingling feeling in the skin; a smaller serving reduces it.",

        // ---------- fat burner ----------
        'thermogenic' => "Buy thermogenic fat burners in Lebanon at Supplement Station, Beirut: original thermogenic formulas from trusted brands, delivered across Lebanon.\n\nThermogenics combine ingredients such as caffeine, green tea extract and peppers to support a fat-loss plan. They work best with a calorie deficit and regular training. Check the caffeine content on the label, and don't combine them with a stimulant pre-workout.",
        'l-carnitine' => "Buy L-carnitine in Lebanon at Supplement Station, Beirut: liquid, capsule and powder L-carnitine from trusted brands, original and delivered across Lebanon.\n\nL-carnitine is a stimulant-free option popular with people following a fat-loss plan, often taken before cardio or training. Since it has no caffeine, it can be combined with other supplements.",
        'cla' => "Buy CLA in Lebanon at Supplement Station, Beirut: original conjugated linoleic acid softgels from trusted brands, delivered across Lebanon.\n\nCLA is a fatty acid found naturally in meat and dairy. It is stimulant-free, so it suits people who want fat-loss support without caffeine; it is usually taken with meals.",

        // ---------- health & wellness ----------
        'vitamins' => "Buy vitamins in Lebanon at Supplement Station, Beirut: multivitamins, vitamin D, vitamin C, B vitamins and more from trusted brands, original and delivered across Lebanon.\n\nA daily multivitamin is an easy way to cover the gaps in your diet, especially when you train often. Take vitamins with a meal for best absorption.",
        'minerals' => "Buy mineral supplements in Lebanon at Supplement Station, Beirut: magnesium, zinc, calcium and other minerals from trusted brands, original and delivered across Lebanon.\n\nPeople who train and sweat a lot often add minerals such as magnesium and zinc to their routine; magnesium is a popular choice in the evening.",
        'omega-3' => "Buy omega 3 fish oil in Lebanon at Supplement Station, Beirut: original omega 3 softgels from trusted brands, delivered across Lebanon.\n\nOmega 3 provides the fatty acids EPA and DHA, found in oily fish. It is one of the most popular everyday supplements for people who don't eat fish often; take it with a meal.",
        'collagen' => "Buy collagen in Lebanon at Supplement Station, Beirut: collagen peptide powders and capsules from trusted brands, original and delivered across Lebanon.\n\nCollagen is the main protein in skin, hair, nails, tendons and joints. Collagen peptides dissolve easily in water, coffee or a shake, and are popular with people who train often and anyone who cares for their skin.",
        'joint-support' => "Buy joint support supplements in Lebanon at Supplement Station, Beirut: glucosamine, chondroitin, MSM and collagen formulas from trusted brands, original and delivered across Lebanon.\n\nJoint formulas are popular with people who lift heavy, run or play sports often. They are usually taken every day over a longer period.",
        'herbs' => "Buy herbal supplements in Lebanon at Supplement Station, Beirut: ashwagandha, tribulus, maca and other herbs from trusted brands, original and delivered across Lebanon.\n\nHerbal supplements have been used for a long time in traditional practice. Read each product's label for its serving, and ask your doctor first if you take medication.",

        // ---------- snacks & carbs ----------
        'protein-bars' => "Buy protein bars in Lebanon at Supplement Station, Beirut: high-protein bars from trusted brands, original and delivered across Lebanon.\n\nProtein bars are an easy snack between meals, after training or on the go, with far more protein than a regular snack bar. Order a box to keep at work, in the car or in your gym bag.",
        'carbs' => "Buy carbohydrate supplements in Lebanon at Supplement Station, Beirut: carb powders from trusted brands, original and delivered across Lebanon.\n\nCarb powders give you energy during long or intense training sessions and help you refuel afterwards. Mix them with water, or add them to your protein shake to make your own gainer.",

        // ---------- accessories ----------
        'shakers' => "Buy protein shakers in Lebanon at Supplement Station, Beirut: shaker bottles from trusted brands.\n\nA good shaker mixes your protein, creatine or pre-workout without lumps and closes tight in your gym bag. Add one to your supplement order and get everything delivered together.",
        'gym-gear' => "Buy gym gear in Lebanon at Supplement Station, Beirut: training accessories to go with your supplements.\n\nAdd them to your order and get everything delivered together, anywhere in Lebanon.",
        'shirts' => "Buy gym shirts in Lebanon at Supplement Station, Beirut: training shirts to wear at the gym and outside it.\n\nAdd one to your supplement order and get everything delivered together, anywhere in Lebanon.",
    ];

    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('image');
        });

        foreach (self::TEXTS as $slug => $text) {
            DB::table('categories')->where('slug', $slug)->whereNull('description')->update(['description' => $text]);
        }

        // The menu's stored copy of the categories and the ready-made pages don't have the texts yet
        Category::forgetCache();
        PageCache::flush();
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
