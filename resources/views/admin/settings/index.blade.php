@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'white-label' }">
    <!-- Tabs -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-2">
        <div class="flex items-center gap-2 overflow-x-auto">
            <button @click="activeTab = 'white-label'" 
                    :class="activeTab === 'white-label' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap">
                White Label
            </button>
            <button @click="activeTab = 'localization'" 
                    :class="activeTab === 'localization' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap">
                Localization
            </button>
            <button @click="activeTab = 'payment'" 
                    :class="activeTab === 'payment' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap">
                Payment Gateways
            </button>
            <button @click="activeTab = 'workflow'" 
                    :class="activeTab === 'workflow' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap">
                Workflow
            </button>
            <button @click="activeTab = 'system'" 
                    :class="activeTab === 'system' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap">
                System
            </button>
            <button @click="activeTab = 'notifications'" 
                    :class="activeTab === 'notifications' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-6 py-3 rounded-xl font-medium transition-all whitespace-nowrap">
                Notifications
            </button>
        </div>
    </div>

    <!-- White Label Tab -->
    <div x-show="activeTab === 'white-label'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">White Label Configuration</h3>
                <p class="text-sm text-slate-500">Customize branding for CodeCanyon SaaS deployment</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.white-label') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="app_name" class="block text-sm font-semibold text-slate-700 mb-2">Application Name *</label>
                        <input type="text" name="app_name" id="app_name" required
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $whiteLabel['app_name'] }}">
                    </div>
                    <div>
                        <label for="company_name" class="block text-sm font-semibold text-slate-700 mb-2">Company Name</label>
                        <input type="text" name="company_name" id="company_name"
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $whiteLabel['company_name'] ?? '' }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="logo_url" class="block text-sm font-semibold text-slate-700 mb-2">Logo URL</label>
                        <input type="url" name="logo_url" id="logo_url"
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $whiteLabel['logo_url'] ?? '' }}" placeholder="https://example.com/logo.png">
                    </div>
                    <div>
                        <label for="favicon_url" class="block text-sm font-semibold text-slate-700 mb-2">Favicon URL</label>
                        <input type="url" name="favicon_url" id="favicon_url"
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $whiteLabel['favicon_url'] ?? '' }}" placeholder="https://example.com/favicon.ico">
                    </div>
                </div>
                <!-- Color Scheme Presets -->
                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Color Scheme Presets</h4>
                    <p class="text-sm text-slate-500 mb-4">Choose a preset color scheme or customize your own</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- Electric Blue Scheme -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="color_scheme" value="electric-blue" 
                                   {{ ($whiteLabel['color_scheme'] ?? 'custom') === 'electric-blue' ? 'checked' : '' }}
                                   class="peer sr-only" 
                                   onchange="applyColorScheme('electric-blue', '#2563EB', '#F97316', '#F1F5F9')">
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-200 hover:border-slate-300">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex gap-2">
                                        <div class="w-8 h-8 rounded-lg" style="background-color: #2563EB;"></div>
                                        <div class="w-8 h-8 rounded-lg" style="background-color: #F97316;"></div>
                                        <div class="w-8 h-8 rounded-lg border border-slate-200" style="background-color: #F1F5F9;"></div>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="font-bold text-slate-800">Electric Blue</h5>
                                        <p class="text-xs text-slate-500">Professional & Modern</p>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-600 space-y-1">
                                    <p><strong>Primary:</strong> Electric Blue (#2563EB)</p>
                                    <p><strong>Secondary:</strong> Pulse Orange (#F97316)</p>
                                    <p><strong>Neutral:</strong> Slate Grey (#F1F5F9)</p>
                                </div>
                            </div>
                        </label>

                        <!-- Deep Indigo Scheme -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="color_scheme" value="deep-indigo" 
                                   {{ ($whiteLabel['color_scheme'] ?? 'custom') === 'deep-indigo' ? 'checked' : '' }}
                                   class="peer sr-only"
                                   onchange="applyColorScheme('deep-indigo', '#4F46E5', '#06B6D4', '#F8FAFC')">
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-200 hover:border-slate-300">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex gap-2">
                                        <div class="w-8 h-8 rounded-lg" style="background-color: #4F46E5;"></div>
                                        <div class="w-8 h-8 rounded-lg" style="background-color: #06B6D4;"></div>
                                        <div class="w-8 h-8 rounded-lg border border-slate-200" style="background-color: #F8FAFC;"></div>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="font-bold text-slate-800">Deep Indigo</h5>
                                        <p class="text-xs text-slate-500">Elegant & Sophisticated</p>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-600 space-y-1">
                                    <p><strong>Primary:</strong> Deep Indigo (#4F46E5)</p>
                                    <p><strong>Secondary:</strong> Neon Cyan (#06B6D4)</p>
                                    <p><strong>Neutral:</strong> Ghost White (#F8FAFC)</p>
                                </div>
                            </div>
                        </label>

                        <!-- Dark Slate Scheme -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="color_scheme" value="dark-slate" 
                                   {{ ($whiteLabel['color_scheme'] ?? 'custom') === 'dark-slate' ? 'checked' : '' }}
                                   class="peer sr-only"
                                   onchange="applyColorScheme('dark-slate', '#0F172A', '#84CC16', '#FFFFFF')">
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-slate-700 peer-checked:ring-2 peer-checked:ring-slate-200 hover:border-slate-300">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex gap-2">
                                        <div class="w-8 h-8 rounded-lg" style="background-color: #0F172A;"></div>
                                        <div class="w-8 h-8 rounded-lg" style="background-color: #84CC16;"></div>
                                        <div class="w-8 h-8 rounded-lg border border-slate-200" style="background-color: #FFFFFF;"></div>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="font-bold text-slate-800">Dark Slate</h5>
                                        <p class="text-xs text-slate-500">Bold & Modern</p>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-600 space-y-1">
                                    <p><strong>Primary:</strong> Dark Slate (#0F172A)</p>
                                    <p><strong>Secondary:</strong> Volt Green (#84CC16)</p>
                                    <p><strong>Neutral:</strong> Pure White (#FFFFFF)</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Custom Colors -->
                    <div class="pt-4 border-t border-slate-200">
                        <label class="relative cursor-pointer mb-4 block">
                            <input type="radio" name="color_scheme" value="custom" 
                                   {{ ($whiteLabel['color_scheme'] ?? 'custom') === 'custom' ? 'checked' : '' }}
                                   class="peer sr-only"
                                   onchange="enableCustomColors()">
                            <div class="border-2 rounded-xl p-4 transition-all peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-200 hover:border-slate-300">
                                <h5 class="font-bold text-slate-800 mb-2">Custom Colors</h5>
                                <p class="text-sm text-slate-500">Define your own color scheme</p>
                            </div>
                        </label>
                        
                        <div id="custom-colors" class="grid grid-cols-3 gap-6 {{ ($whiteLabel['color_scheme'] ?? 'custom') !== 'custom' ? 'opacity-50 pointer-events-none' : '' }}">
                            <div>
                                <label for="primary_color" class="block text-sm font-semibold text-slate-700 mb-2">Primary Color</label>
                                <input type="color" name="primary_color" id="primary_color"
                                       class="w-full h-12 border border-slate-300 rounded-xl cursor-pointer" 
                                       value="{{ $whiteLabel['primary_color'] }}">
                                <p class="text-xs text-slate-500 mt-1">Sidebar, Primary Buttons, Active States</p>
                            </div>
                            <div>
                                <label for="secondary_color" class="block text-sm font-semibold text-slate-700 mb-2">Secondary Color</label>
                                <input type="color" name="secondary_color" id="secondary_color"
                                       class="w-full h-12 border border-slate-300 rounded-xl cursor-pointer" 
                                       value="{{ $whiteLabel['secondary_color'] }}">
                                <p class="text-xs text-slate-500 mt-1">Timer, Accept Buttons, Urgent Badges</p>
                            </div>
                            <div>
                                <label for="neutral_color" class="block text-sm font-semibold text-slate-700 mb-2">Neutral Color</label>
                                <input type="color" name="neutral_color" id="neutral_color"
                                       class="w-full h-12 border border-slate-300 rounded-xl cursor-pointer" 
                                       value="{{ $whiteLabel['neutral_color'] ?? '#F1F5F9' }}">
                                <p class="text-xs text-slate-500 mt-1">Dashboard Background, Cards</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="support_email" class="block text-sm font-semibold text-slate-700 mb-2">Support Email</label>
                        <input type="email" name="support_email" id="support_email"
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $whiteLabel['support_email'] ?? '' }}">
                    </div>
                    <div>
                        <label for="support_phone" class="block text-sm font-semibold text-slate-700 mb-2">Support Phone</label>
                        <input type="text" name="support_phone" id="support_phone"
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $whiteLabel['support_phone'] ?? '' }}">
                    </div>
                </div>
                <div>
                    <label for="company_address" class="block text-sm font-semibold text-slate-700 mb-2">Company Address</label>
                    <textarea name="company_address" id="company_address" rows="2"
                              class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ $whiteLabel['company_address'] ?? '' }}</textarea>
                </div>
                <div>
                    <label for="company_website" class="block text-sm font-semibold text-slate-700 mb-2">Company Website</label>
                    <input type="url" name="company_website" id="company_website"
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ $whiteLabel['company_website'] ?? '' }}">
                </div>
                <div>
                    <label for="footer_text" class="block text-sm font-semibold text-slate-700 mb-2">Footer Text</label>
                    <input type="text" name="footer_text" id="footer_text"
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ $whiteLabel['footer_text'] ?? '' }}">
                </div>

                <!-- SEO Section -->
                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">SEO Settings</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="meta_title" class="block text-sm font-semibold text-slate-700 mb-2">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $whiteLabel['meta_title'] ?? '' }}">
                        </div>
                        <div>
                            <label for="meta_keywords" class="block text-sm font-semibold text-slate-700 mb-2">Meta Keywords</label>
                            <input type="text" name="meta_keywords" id="meta_keywords"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $whiteLabel['meta_keywords'] ?? '' }}" placeholder="repair, service, management">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="meta_description" class="block text-sm font-semibold text-slate-700 mb-2">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="2"
                                  class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ $whiteLabel['meta_description'] ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Open Graph Section -->
                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Open Graph Settings</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="og_title" class="block text-sm font-semibold text-slate-700 mb-2">Open Graph Title</label>
                            <input type="text" name="og_title" id="og_title"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $whiteLabel['og_title'] ?? '' }}">
                        </div>
                        <div>
                            <label for="og_image" class="block text-sm font-semibold text-slate-700 mb-2">Open Graph Image URL</label>
                            <input type="url" name="og_image" id="og_image"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $whiteLabel['og_image'] ?? '' }}" placeholder="https://example.com/og-image.jpg">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="og_description" class="block text-sm font-semibold text-slate-700 mb-2">Open Graph Description</label>
                        <textarea name="og_description" id="og_description" rows="2"
                                  class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ $whiteLabel['og_description'] ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Twitter Cards Section -->
                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Twitter Cards Settings</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="twitter_title" class="block text-sm font-semibold text-slate-700 mb-2">Twitter Cards Title</label>
                            <input type="text" name="twitter_title" id="twitter_title"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $whiteLabel['twitter_title'] ?? '' }}">
                        </div>
                        <div>
                            <label for="twitter_image" class="block text-sm font-semibold text-slate-700 mb-2">Twitter Cards Image URL</label>
                            <input type="url" name="twitter_image" id="twitter_image"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $whiteLabel['twitter_image'] ?? '' }}" placeholder="https://example.com/twitter-image.jpg">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="twitter_description" class="block text-sm font-semibold text-slate-700 mb-2">Twitter Cards Description</label>
                        <textarea name="twitter_description" id="twitter_description" rows="2"
                                  class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ $whiteLabel['twitter_description'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                        Save White Label Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Localization Tab -->
    <div x-show="activeTab === 'localization'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Localization Settings</h3>
                <p class="text-sm text-slate-500">Configure language, currency, timezone, and regional settings</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.localization') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="default_language" class="block text-sm font-semibold text-slate-700 mb-2">Default Language</label>
                        <select name="default_language" id="default_language" 
                                class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="en" {{ ($localization['default_language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ar" {{ ($localization['default_language'] ?? '') === 'ar' ? 'selected' : '' }}>Arabic</option>
                            <option value="he" {{ ($localization['default_language'] ?? '') === 'he' ? 'selected' : '' }}>Hebrew</option>
                            <option value="hi" {{ ($localization['default_language'] ?? '') === 'hi' ? 'selected' : '' }}>Hindi</option>
                        </select>
                    </div>
                    <div>
                        <label for="timezone" class="block text-sm font-semibold text-slate-700 mb-2">Application Time Zone</label>
                        <select name="timezone" id="timezone" 
                                class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="UTC" {{ ($localization['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ ($localization['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>EST (America/New_York)</option>
                            <option value="America/Chicago" {{ ($localization['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' }}>CST (America/Chicago)</option>
                            <option value="America/Denver" {{ ($localization['timezone'] ?? '') === 'America/Denver' ? 'selected' : '' }}>MST (America/Denver)</option>
                            <option value="America/Los_Angeles" {{ ($localization['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>PST (America/Los_Angeles)</option>
                            <option value="Asia/Kolkata" {{ ($localization['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : '' }}>IST (Asia/Kolkata)</option>
                            <option value="Europe/London" {{ ($localization['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>GMT (Europe/London)</option>
                            <option value="Europe/Paris" {{ ($localization['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : '' }}>CET (Europe/Paris)</option>
                            <option value="Asia/Dubai" {{ ($localization['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' }}>GST (Asia/Dubai)</option>
                        </select>
                    </div>
                </div>

                <!-- Currency Configuration -->
                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Currency Configuration</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="default_currency" class="block text-sm font-semibold text-slate-700 mb-2">Currency</label>
                            <select name="default_currency" id="default_currency" 
                                    class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="USD" {{ ($localization['default_currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ ($localization['default_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ ($localization['default_currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="INR" {{ ($localization['default_currency'] ?? '') === 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                <option value="AED" {{ ($localization['default_currency'] ?? '') === 'AED' ? 'selected' : '' }}>AED - UAE Dirham</option>
                                <option value="SAR" {{ ($localization['default_currency'] ?? '') === 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal</option>
                                <option value="CAD" {{ ($localization['default_currency'] ?? '') === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                <option value="AUD" {{ ($localization['default_currency'] ?? '') === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                            </select>
                        </div>
                        <div>
                            <label for="currency_symbol" class="block text-sm font-semibold text-slate-700 mb-2">Currency Symbol</label>
                            <input type="text" name="currency_symbol" id="currency_symbol" maxlength="10"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $localization['currency_symbol'] ?? '$' }}" placeholder="$">
                            <p class="mt-1 text-xs text-slate-500">Symbol to display with currency amounts</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="currency_symbol_alignment" class="block text-sm font-semibold text-slate-700 mb-2">Currency Symbol Alignment</label>
                        <select name="currency_symbol_alignment" id="currency_symbol_alignment" 
                                class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="left" {{ ($localization['currency_symbol_alignment'] ?? 'left') === 'left' ? 'selected' : '' }}>Left (e.g., $100)</option>
                            <option value="right" {{ ($localization['currency_symbol_alignment'] ?? '') === 'right' ? 'selected' : '' }}>Right (e.g., 100$)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 pt-6 border-t border-slate-200">
                    <div>
                        <label for="date_format" class="block text-sm font-semibold text-slate-700 mb-2">Date Format</label>
                        <select name="date_format" id="date_format" 
                                class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="Y-m-d" {{ ($localization['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                            <option value="m/d/Y" {{ ($localization['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            <option value="d/m/Y" {{ ($localization['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="d-m-Y" {{ ($localization['date_format'] ?? '') === 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                        </select>
                    </div>
                    <div>
                        <label for="time_format" class="block text-sm font-semibold text-slate-700 mb-2">Time Format</label>
                        <select name="time_format" id="time_format" 
                                class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="H:i" {{ ($localization['time_format'] ?? 'H:i') === 'H:i' ? 'selected' : '' }}>24 Hour (HH:MM)</option>
                            <option value="h:i A" {{ ($localization['time_format'] ?? '') === 'h:i A' ? 'selected' : '' }}>12 Hour (HH:MM AM/PM)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                        Save Localization Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Gateways Tab -->
    <div x-show="activeTab === 'payment'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Payment Gateway Configuration</h3>
                <p class="text-sm text-slate-500">Enable/disable payment gateways and configure credentials</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.payment-gateways') }}" class="space-y-6">
                @csrf
                
                <!-- Stripe -->
                <div class="border border-slate-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h4 class="text-lg font-bold text-slate-800">Stripe</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="stripe_enabled" value="1" {{ $paymentGateways['stripe_enabled'] ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Publishable Key</label>
                            <input type="text" name="stripe_key" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['stripe_key'] ?? '' }}" placeholder="pk_test_...">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Secret Key</label>
                            <input type="password" name="stripe_secret" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['stripe_secret'] ?? '' }}" placeholder="sk_test_...">
                        </div>
                    </div>
                </div>

                <!-- Razorpay -->
                <div class="border border-slate-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h4 class="text-lg font-bold text-slate-800">Razorpay</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="razorpay_enabled" value="1" {{ $paymentGateways['razorpay_enabled'] ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Key ID</label>
                            <input type="text" name="razorpay_key" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['razorpay_key'] ?? '' }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Key Secret</label>
                            <input type="password" name="razorpay_secret" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['razorpay_secret'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- PhonePe -->
                <div class="border border-slate-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h4 class="text-lg font-bold text-slate-800">PhonePe</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="phonepe_enabled" value="1" {{ $paymentGateways['phonepe_enabled'] ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Merchant ID</label>
                            <input type="text" name="phonepe_merchant_id" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['phonepe_merchant_id'] ?? '' }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Salt Key</label>
                            <input type="password" name="phonepe_salt_key" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['phonepe_salt_key'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Paytm -->
                <div class="border border-slate-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h4 class="text-lg font-bold text-slate-800">Paytm</h4>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="paytm_enabled" value="1" {{ $paymentGateways['paytm_enabled'] ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Merchant ID</label>
                            <input type="text" name="paytm_merchant_id" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['paytm_merchant_id'] ?? '' }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Merchant Key</label>
                            <input type="password" name="paytm_merchant_key" 
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                                   value="{{ $paymentGateways['paytm_merchant_key'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Cash & COD -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="border border-slate-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-bold text-slate-800 mb-1">Cash Payment</h4>
                                <p class="text-sm text-slate-500">Allow cash payments</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="cash_enabled" value="1" {{ $paymentGateways['cash_enabled'] ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-bold text-slate-800 mb-1">Cash on Delivery</h4>
                                <p class="text-sm text-slate-500">Allow COD payments</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="cod_enabled" value="1" {{ $paymentGateways['cod_enabled'] ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                        Save Payment Gateway Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Workflow Tab -->
    <div x-show="activeTab === 'workflow'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Workflow Settings</h3>
                <p class="text-sm text-slate-500">Configure automation timeouts and requirements</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.workflow') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="triage_timeout_minutes" class="block text-sm font-semibold text-slate-700 mb-2">Triage Timeout (minutes)</label>
                        <input type="number" name="triage_timeout_minutes" id="triage_timeout_minutes" min="1" max="60" 
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $workflow['triage_timeout_minutes'] }}">
                        <p class="mt-1 text-xs text-slate-500">Time before auto-assignment</p>
                    </div>
                    <div>
                        <label for="job_offer_timeout_minutes" class="block text-sm font-semibold text-slate-700 mb-2">Job Offer Timeout (minutes)</label>
                        <input type="number" name="job_offer_timeout_minutes" id="job_offer_timeout_minutes" min="1" max="60" 
                               class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                               value="{{ $workflow['job_offer_timeout_minutes'] }}">
                        <p class="mt-1 text-xs text-slate-500">Time for technician to accept</p>
                    </div>
                </div>
                <div>
                    <label for="awaiting_payment_timeout_hours" class="block text-sm font-semibold text-slate-700 mb-2">Wait for Awaiting Payment for (hours)</label>
                    <input type="number" name="awaiting_payment_timeout_hours" id="awaiting_payment_timeout_hours" min="1" max="168" 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ $workflow['awaiting_payment_timeout_hours'] ?? 24 }}">
                    <p class="mt-1 text-xs text-slate-500">Hours to wait before canceling unpaid orders</p>
                </div>
                <div>
                    <label for="tax_rate" class="block text-sm font-semibold text-slate-700 mb-2">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" id="tax_rate" step="0.01" min="0" max="100" 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ $workflow['tax_rate'] }}">
                </div>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="require_photos" id="require_photos" value="1" {{ $workflow['require_photos'] ? 'checked' : '' }} 
                               class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="require_photos" class="ml-3 text-sm font-medium text-slate-700">Require Photos on Booking</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="enable_service_tips" id="enable_service_tips" value="1" {{ $workflow['enable_service_tips'] ?? false ? 'checked' : '' }} 
                               class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="enable_service_tips" class="ml-3 text-sm font-medium text-slate-700">Enable Service Delivery Tip Amount</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="enable_service_ratings" id="enable_service_ratings" value="1" {{ $workflow['enable_service_ratings'] ?? true ? 'checked' : '' }} 
                               class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="enable_service_ratings" class="ml-3 text-sm font-medium text-slate-700">Enable Service Ratings</label>
                    </div>
                </div>
                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                        Save Workflow Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- System Tab -->
    <div x-show="activeTab === 'system'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">System Settings</h3>
                <p class="text-sm text-slate-500">Configure system-wide settings</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.system') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="upload_image_quality" class="block text-sm font-semibold text-slate-700 mb-2">Upload Image Quality (%)</label>
                    <input type="number" name="upload_image_quality" id="upload_image_quality" min="1" max="100" 
                           class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                           value="{{ $system['upload_image_quality'] ?? 85 }}">
                    <p class="mt-1 text-xs text-slate-500">Image compression quality (1-100). Higher = better quality but larger file size</p>
                </div>

                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Invoice Generation</h4>
                    <div class="flex items-center mb-4">
                        <input type="checkbox" name="invoice_generation" id="invoice_generation" value="1" {{ $system['invoice_generation'] ?? true ? 'checked' : '' }} 
                               class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="invoice_generation" class="ml-3 text-sm font-medium text-slate-700">Enable Invoice Generation</label>
                    </div>
                    <div>
                        <label for="invoice_template" class="block text-sm font-semibold text-slate-700 mb-2">Invoice Template</label>
                        <select name="invoice_template" id="invoice_template" 
                                class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="default" {{ ($system['invoice_template'] ?? 'default') === 'default' ? 'selected' : '' }}>Default</option>
                            <option value="modern" {{ ($system['invoice_template'] ?? '') === 'modern' ? 'selected' : '' }}>Modern</option>
                            <option value="minimal" {{ ($system['invoice_template'] ?? '') === 'minimal' ? 'selected' : '' }}>Minimal</option>
                            <option value="professional" {{ ($system['invoice_template'] ?? '') === 'professional' ? 'selected' : '' }}>Professional</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Google Maps API Configuration</h4>
                    <div class="space-y-4">
                        <div>
                            <label for="google_maps_api_key" class="block text-sm font-semibold text-slate-700 mb-2">Google Maps API Key</label>
                            <input type="text" name="google_maps_api_key" id="google_maps_api_key"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-mono text-sm" 
                                   value="{{ $system['google_maps_api_key'] ?? '' }}" 
                                   placeholder="AIzaSyCX5KEm1rEGxp05USWcE2XwUlh9KiVnhVs">
                            <p class="mt-1 text-xs text-slate-500">Get your API key from <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a></p>
                        </div>
                        <div>
                            <label for="google_maps_api_key_restriction" class="block text-sm font-semibold text-slate-700 mb-2">API Key Restriction Type</label>
                            <select name="google_maps_api_key_restriction" id="google_maps_api_key_restriction" 
                                    class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="none" {{ ($system['google_maps_api_key_restriction'] ?? 'none') === 'none' ? 'selected' : '' }}>No Restriction</option>
                                <option value="http" {{ ($system['google_maps_api_key_restriction'] ?? '') === 'http' ? 'selected' : '' }}>HTTP Restriction</option>
                                <option value="ip" {{ ($system['google_maps_api_key_restriction'] ?? '') === 'ip' ? 'selected' : '' }}>IP Restriction</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Configure restriction type in Google Cloud Console</p>
                        </div>
                        <div id="http-restrictions-container" style="display: {{ ($system['google_maps_api_key_restriction'] ?? 'none') === 'http' ? 'block' : 'none' }};">
                            <label for="google_maps_http_restrictions" class="block text-sm font-semibold text-slate-700 mb-2">HTTP Restrictions (one per line)</label>
                            <textarea name="google_maps_http_restrictions" id="google_maps_http_restrictions" rows="4"
                                      class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-mono text-sm"
                                      placeholder="https://yourdomain.com&#10;https://www.yourdomain.com">{{ $system['google_maps_http_restrictions'] ?? '' }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">Enter allowed HTTP referrers (one per line). Configure these in Google Cloud Console API restrictions.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                        Save System Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function applyColorScheme(scheme, primary, secondary, neutral) {
            document.getElementById('primary_color').value = primary;
            document.getElementById('secondary_color').value = secondary;
            document.getElementById('neutral_color').value = neutral;
            
            // Enable custom colors section
            document.getElementById('custom-colors').classList.remove('opacity-50', 'pointer-events-none');
        }

        function enableCustomColors() {
            document.getElementById('custom-colors').classList.remove('opacity-50', 'pointer-events-none');
        }

        // Disable custom colors if preset is selected
        document.addEventListener('DOMContentLoaded', function() {
            const presetRadios = document.querySelectorAll('input[name="color_scheme"][value!="custom"]');
            const customRadio = document.querySelector('input[name="color_scheme"][value="custom"]');
            const customColorsDiv = document.getElementById('custom-colors');

            presetRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        customColorsDiv.classList.add('opacity-50', 'pointer-events-none');
                    }
                });
            });

            customRadio.addEventListener('change', function() {
                if (this.checked) {
                    customColorsDiv.classList.remove('opacity-50', 'pointer-events-none');
                }
            });

            // Initial state
            if (!customRadio.checked) {
                customColorsDiv.classList.add('opacity-50', 'pointer-events-none');
            }
        });
    </script>
    
    <script>
        // Show/hide HTTP restrictions based on restriction type
        document.addEventListener('DOMContentLoaded', function() {
            const restrictionSelect = document.getElementById('google_maps_api_key_restriction');
            const httpContainer = document.getElementById('http-restrictions-container');
            
            if (restrictionSelect && httpContainer) {
                restrictionSelect.addEventListener('change', function() {
                    httpContainer.style.display = this.value === 'http' ? 'block' : 'none';
                });
            }
        });
    </script>

    <!-- Notifications Tab -->
    <div x-show="activeTab === 'notifications'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Push Notification Settings</h3>
                <p class="text-sm text-slate-500">Configure push notifications for Customer and Technician applications</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.notifications') }}" class="space-y-6">
                @csrf
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                        <div>
                            <h4 class="font-semibold text-slate-800 mb-1">Customer Application Push Notifications</h4>
                            <p class="text-sm text-slate-500">Enable push notifications for customer mobile app</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="customer_push_enabled" value="1" {{ $notifications['customer_push_enabled'] ?? true ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                        <div>
                            <h4 class="font-semibold text-slate-800 mb-1">Technician Application Push Notifications</h4>
                            <p class="text-sm text-slate-500">Enable push notifications for technician mobile app</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="technician_push_enabled" value="1" {{ $notifications['technician_push_enabled'] ?? true ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Push Notification Credentials</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="push_notification_key" class="block text-sm font-semibold text-slate-700 mb-2">Push Notification Key</label>
                            <input type="text" name="push_notification_key" id="push_notification_key"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $notifications['push_notification_key'] ?? '' }}" placeholder="FCM Server Key or APNS Key">
                        </div>
                        <div>
                            <label for="push_notification_secret" class="block text-sm font-semibold text-slate-700 mb-2">Push Notification Secret</label>
                            <input type="password" name="push_notification_secret" id="push_notification_secret"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                   value="{{ $notifications['push_notification_secret'] ?? '' }}" placeholder="FCM Secret or APNS Secret">
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Configure Firebase Cloud Messaging (FCM) or Apple Push Notification Service (APNS) credentials</p>
                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-medium shadow-lg hover:shadow-xl">
                        Save Notification Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
