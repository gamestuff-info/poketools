/**
 * Set the page title, prefixing with the site name.
 * @param title The page title, or a list of title components (e.g. `"Abilities"` or `["Abilities", "Arena Trap"]`)
 */
export default function setPageTitle(title: string | Array<string>) {
    if (!Array.isArray(title)) {
        title = [title];
    }
    title.unshift('Pokétools');
    document.title = title.join(' - ');
}
