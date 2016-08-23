<?php

// инициализация
if (!is_dir('cache')) {
	mkdir('cache');
}
if (!is_dir('cache/articles')) {
	mkdir('cache/articles');
}