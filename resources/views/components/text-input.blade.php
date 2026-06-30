@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 bg-white text-gray-900 dark:bg-black dark:text-white focus:border-gray-500 dark:focus:border-white focus:ring-gray-500 dark:focus:ring-white focus:ring-opacity-50 rounded-md shadow-sm']) }}>
