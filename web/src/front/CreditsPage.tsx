import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';

export default function CreditsPage(props: {}) {
    return (
        <div>
            <div className="d-flex justify-content-center">
                <img className="pkt-logo" src={getAssetUrl('logo-cropped.svg', AssetPackage.MEDIA)} alt=""/>
            </div>

            <h1>Credits</h1>
            <p>This project owes a great debt to these fine folks:</p>
            <ul>
                <li>
                    <a href="https://veekun.com/">Veekun</a>, for much of the original data
                </li>
                <li>
                    <a href="https://bulbapedia.bulbagarden.net/">Bulbapedia</a>, for providing explanations of the
                    various mechanics.
                </li>
                <li>
                    <a href="https://fontawesome.com/">Font Awesome</a>, for the generic icons on this site
                </li>
            </ul>
        </div>
    );
}
