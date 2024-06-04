function Button({label, clickHandler, icon, colorClasses = "bg-gray-600 hover:bg-gray-500 focus:ring-gray-500"}) {
    const defaultClasses = "inline-flex items-center justify-center px-3 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 text-white focus:ring-2 focus:ring-offset-2 shadow-sm"

    return (
        <button type="button" onClick={clickHandler} className={defaultClasses + ' ' + colorClasses}>
            {icon()}{label}
        </button>
    );
}

export default Button;
