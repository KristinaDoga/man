<? /*
<div id="<?php print $block_html_id; ?>" class="<?php print $classes; ?>"<?php print $attributes; ?>>

	<?php print render($title_prefix); ?>
		<?php if ($block->subject): ?>
			<p class="block_heading"><?php print $title_attributes; ?><?php print $block->subject ?></p>
		<?php endif;?>
	<?php print render($title_suffix); ?>

	<div class="wrapper">
		<div class="image"></div>
        <div class="test-cnt">
        	<div class="test">
			    <p class="ex-h2 title-test">Тест определения стадии алкоголизма</p>
			    <form class="test-form show" action="" name="formSum">
			        <div class="question-1">
			            <div class="question-text">Как часто выпивает ваш близкий?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a1-1" type="radio" name="question-1" value="0" /><label for="a1-1">По праздникам</label></div>
			                <div class="answer-2"><input id="a1-2" type="radio" name="question-1" value="1" /><label for="a1-2">Пару раз в месяц</label></div>
			                <div class="answer-3"><input id="a1-3" type="radio" name="question-1" value="2" /><label for="a1-3">1-2 раза в неделю</label></div>
			                <div class="answer-4"><input id="a1-4" type="radio" name="question-1" value="3" /><label for="a1-4">Несколько раз в неделю</label></div>
			            </div>
			        </div>
			        <div class="question-2">
			            <div class="question-text">Ищет ли ваш близкий постоянные поводы, чтобы выпить?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a2-1" type="radio" name="question-2" value="1" /><label for="a2-1">Да</label></div>
			                <div class="answer-2"><input id="a2-2" type="radio" name="question-2" value="0" /><label for="a2-2">Нет</label></div>
			            </div>
			        </div>
			        <div class="question-3">
			            <div class="question-text">Обосновывает ли ваш близкий свое пристрастие к алкоголю тем, что пьет не больше других?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a3-1" type="radio" name="question-3" value="1" /><label for="a3-1">Да, это является постоянной отговоркой</label></div>
			                <div class="answer-2"><input id="a3-2" type="radio" name="question-3" value="0" /><label for="a3-2">Нет</label></div>
			            </div>
			        </div>
			        <div class="question-4">
			            <div class="question-text">Заметили ли вы, что у вашего близкого повысилась терпимость к алкоголю (отсутствие рвоты при употреблении большой дозы, желание опохмелиться)?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a4-1" type="radio" name="question-4" value="2" /><label for="a4-1">Да</label></div>
			                <div class="answer-2"><input id="a4-2" type="radio" name="question-4" value="0" /><label for="a4-2">Нет</label></div>
			                <div class="answer-3"><input id="a4-3" type="radio" name="question-4" value="1" /><label for="a4-3">Указанные симптомы проявляются редко</label></div>
			            </div>
			        </div>
			        <div class="question-5">
			            <div class="question-text">Замечаете ли вы изменения в поведении (агрессия, нервозность, вспыльчивость, которых раньше не было)?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a5-1" type="radio" name="question-5" value="2" /><label for="a5-1">Да, поведение изменилось кардинально</label></div>
			                <div class="answer-2"><input id="a5-2" type="radio" name="question-5" value="1" /><label for="a5-2">Есть незначительные изменения</label></div>
			                <div class="answer-3"><input id="a5-3" type="radio" name="question-5" value="0" /><label for="a5-3">Нет, изменений в поведении нет</label></div>
			            </div>
			        </div>
			        <div class="question-6">
			            <div class="question-text">Стал ли человек более равнодушным к работе, семье, любимым занятиям?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a6-1" type="radio" name="question-6" value="3" /><label for="a6-1">Да</label></div>
			                <div class="answer-2"><input id="a6-2" type="radio" name="question-6" value="1" /><label for="a6-2">Равнодушие появляется только во время приема алкоголя</label></div>
			                <div class="answer-3"><input id="a6-3" type="radio" name="question-6" value="0" /><label for="a6-3">Поведение не изменилось</label></div>
			            </div>
			        </div>
			        <div class="question-7">
			            <div class="question-text">Замечаете ли вы, что человек стал выпивать в одиночку?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a7-1" type="radio" name="question-7" value="2" /><label for="a7-1">Да</label></div>
			                <div class="answer-2"><input id="a7-2" type="radio" name="question-7" value="0" /><label for="a7-2">Нет</label></div>
			            </div>
			        </div>
			        <div class="question-8">
			            <div class="question-text">Есть ли у вас беспокойство по поводу количества и частоты приема алкоголя вашим близким?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a8-1" type="radio" name="question-8" value="2" /><label for="a8-1">Да</label></div>
			                <div class="answer-2"><input id="a8-2" type="radio" name="question-8" value="1" /><label for="a8-2">Он выпивает не больше других</label></div>
			                <div class="answer-3"><input id="a8-3" type="radio" name="question-8" value="0" /><label for="a8-3">Нет</label></div>
			            </div>
			        </div>
			        <div class="question-9">
			            <div class="question-text">Раздражается ли человек, если не может выпить либо если ему предстоит побывать на безалкогольном мероприятии?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a9-1" type="radio" name="question-9" value="0" /><label for="a9-1">Поведение не меняется</label></div>
			                <div class="answer-2"><input id="a9-2" type="radio" name="question-9" value="1" /><label for="a9-2">Присутствует небольшое раздражение</label></div>
			                <div class="answer-3"><input id="a9-3" type="radio" name="question-9" value="2" /><label for="a9-3">Человек злится, раздражается и отказывается посещать такое мероприятие</label></div>
			            </div>
			        </div>
			        <div class="question-10">
			            <div class="question-text">Находит ли человек оправдания каждому приему алкоголя?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a10-1" type="radio" name="question-10" value="2" /><label for="a10-1">Да, он оправдывает прием алкоголя стрессом, неприятностями, желанием расслабиться</label></div>
			                <div class="answer-2"><input id="a10-2" type="radio" name="question-10" value="1" /><label for="a10-2">Он прислушивается к мнению со стороны и ограничивает прием спиртных напитков</label></div>
			                <div class="answer-3"><input id="a10-3" type="radio" name="question-10" value="3" /><label for="a10-3">Нет, человек пьет вопреки всем уговорам и просьбам</label></div>
			            </div>
			        </div>
			        <div class="question-11">
			            <div class="question-text">Наблюдается ли тенденция к увеличению дозы алкоголя, пьет ли человек больше, чем в прошлом году\месяце?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a11-1" type="radio" name="question-11" value="3" /><label for="a11-1">Да, стал пить больше</label></div>
			                <div class="answer-2"><input id="a11-2" type="radio" name="question-11" value="1" /><label for="a11-2">Нет, количество алкоголя не изменилось</label></div>
			            </div>
			        </div>
			        <div class="question-12">
			            <div class="question-text">Провоцировал ли алкоголь неприятности, вызовы полиции и пр.?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a12-1" type="radio" name="question-12" value="1" /><label for="a12-1">Были единичные случаи</label></div>
			                <div class="answer-2"><input id="a12-2" type="radio" name="question-12" value="0" /><label for="a12-2">Нет, таких ситуаций не случалось</label></div>
			                <div class="answer-3"><input id="a12-3" type="radio" name="question-12" value="3" /><label for="a12-3">Да, прием алкоголя постоянно заканчивается вызовом полиции и другими неприятностями</label></div>
			            </div>
			        </div>
			        <div class="question-13">
			            <div class="question-text">Бывали ли у вашего близкого запои?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a13-1" type="radio" name="question-13" value="2" /><label for="a13-1">Да</label></div>
			                <div class="answer-2"><input id="a13-2" type="radio" name="question-13" value="0" /><label for="a13-2">Нет</label></div>
			            </div>
			        </div>
			        <div class="question-14">
			            <div class="question-text">Обнаруживались ли у человека проблема со здоровьем из-за пьянок (частые «больничные», невозможность сосредоточиться, пр.) Приводило ли это к претензиям со стороны сослуживцев, начальников?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a14-1" type="radio" name="question-14" value="1" /><label for="a14-1">Были единичные случаи</label></div>
			                <div class="answer-2"><input id="a14-2" type="radio" name="question-14" value="2" /><label for="a14-2">Да, такое случается часто</label></div>
			                <div class="answer-3"><input id="a14-3" type="radio" name="question-14" value="0" /><label for="a14-3">Нет, проблем не возникало</label></div>
			            </div>
			        </div>
			        <div class="question-15">
			            <div class="question-text">Не случалось ли вашему близкому госпитализироваться по случаю повреждений, травм, произошедших во время выпивок, вызванных, возможно, тем, что он выпил слишком много?</div>
			            <div class="answer-wrapper">
			                <div class="answer-1"><input id="a15-1" type="radio" name="question-15" value="0" /><label for="a15-1">Нет</label></div>
			                <div class="answer-2"><input id="a15-2" type="radio" name="question-15" value="1" /><label for="a15-2">Были единичные случаи</label></div>
			                <div class="answer-3"><input id="a15-3" type="radio" name="question-15" value="2" /><label for="a15-3">Да, такое случается часто</label></div>
			            </div>
			        </div>
			        <input class="btn btn-submit" type="button" value="Показать результаты" style="cursor: pointer;" />
			    </form>
			</div>
        </div>
	</div>
	<div class="content"<?php print $content_attributes; ?>>
		<p class="ex-h2 callToResultsHeading">Для получения результатов оставьте Ваш номер телефона и email</p>
		<?php print $content ?>
	</div>

</div> */ ?>
<div class="formdesigner-widget" data-id="240651"></div>