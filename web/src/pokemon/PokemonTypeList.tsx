import './PokemonTypeList.scss';
import TypeLabel from '../type/TypeLabel';

export default function PokemonTypeList(props: { types: Array<ApiRecord.Pokemon.PokemonType> }) {
    const {types} = props;
    types.sort((a, b) => a.position - b.position);
    return (
        <ul className="pkt-pokemon-type">
            {types.map(type => (
                <li key={type.type['@id']}><TypeLabel type={type.type}/></li>
            ))}
        </ul>
    );
}
