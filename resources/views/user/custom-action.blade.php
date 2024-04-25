@if ( !$row->hasRole('Super Admin') )
    @if($row->is_suspended)
        <a href="{{route('user.suspension', $row->id)}}" class="text-yellow-600 hover:text-yellow-900" title="Resume (allow) use of the account">{{ __('Resume') }}</a>
    @else
        <a href="{{route('user.suspension', $row->id)}}" class="text-yellow-600 hover:text-yellow-900" title="Pause (stop) use of the account">{{ __('Pause') }}</a>
    @endif
    <span class="text-gray-400 px-1">|</span>
    <a href="{{route('user.edit', $row->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
    <span class="text-gray-400 px-1">|</span>
    <a href="{{route('user.destroy', $row->id)}}" x-on:click.prevent="confirmThenDelete($el)" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</a>
@endif
