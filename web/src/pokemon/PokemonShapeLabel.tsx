import EntityLabel from '../common/components/EntityLabel';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';

export default function PokemonShapeLabel(props: { shape: ApiRecord.Pokemon.PokemonShapeInVersionGroup }) {
    const {shape} = props;
    return (
        <EntityLabel>
            {shape.icon && <EntityLabel.Icon src={getAssetUrl(`shape/${shape.icon}`, AssetPackage.MEDIA)}/>}
            <EntityLabel.Text>{shape.name}</EntityLabel.Text>
        </EntityLabel>
    );
}
