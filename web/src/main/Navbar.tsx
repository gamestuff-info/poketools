import {Dropdown, Form as BsForm, Nav, Navbar as BsNavbar} from 'react-bootstrap';
import {generatePath, Link} from 'react-router-dom';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import Loading from '../common/components/Loading';
import React from 'react';
import {Routes} from '../routes';
import QuickSearch from '../search/QuickSearch';
import './Navbar.scss';

type VersionSelectorProps = {
    versions: Map<number, ApiRecord.Version>,
    currentVersion: ApiRecord.Version,
    onChange: (newVersionId: number) => void,
};

function VersionSelector(props: VersionSelectorProps) {
    const options = [];
    for (const version of props.versions.values()) {
        options.push(<option key={version.id} value={version.id}>{version.name}</option>);
    }
    return (
        <BsForm inline>
            <BsForm.Control
                as="select"
                value={props.currentVersion.id}
                aria-label="Choose Version"
                onChange={e => props.onChange(parseInt(e.target.value))}
            >
                {options}
            </BsForm.Control>
        </BsForm>
    );
}

interface NavbarProps {
    versions?: Map<number, ApiRecord.Version> | null,
    currentVersion?: ApiRecord.Version,
    onVersionChange: (newVersionId: number) => void,
}

export default function Navbar(props: NavbarProps) {
    const ready = props.currentVersion && props.versions;
    return (
        <BsNavbar collapseOnSelect expand="lg"
                  className="pkt-navbar-main"
                  variant="dark"
                  bg="primary"
                  fixed="top"
        >
            <BsNavbar.Brand as={Link} to="/">
                <img className="pkt-logo" src={getAssetUrl('navbar-logo.svg', AssetPackage.MEDIA)} alt=""/>
                &nbsp;
                Pokétools
            </BsNavbar.Brand>
            <BsNavbar.Toggle aria-controls="pkt-navbar-content"/>
            <BsNavbar.Collapse id="pkt-navbar-content">
                {ready &&
                <Nav className="mr-auto">
                    <VersionSelector currentVersion={props.currentVersion!}
                                     versions={props.versions!}
                                     onChange={props.onVersionChange}
                    />
                    <Nav.Link as={Link}
                              to={generatePath(Routes.POKEMON_INDEX, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                        Pokémon
                    </Nav.Link>
                    <Nav.Link as={Link}
                              to={generatePath(Routes.MOVE_INDEX, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                        Moves
                    </Nav.Link>
                    <Nav.Link as={Link}
                              to={generatePath(Routes.TYPE_INDEX, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                        Types
                    </Nav.Link>
                    <Nav.Link as={Link}
                              to={generatePath(Routes.ITEM_INDEX, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                        Items
                    </Nav.Link>
                    <Nav.Link as={Link}
                              to={generatePath(Routes.LOCATION_INDEX, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                        Locations
                    </Nav.Link>
                    <Nav.Link as={Link}
                              to={generatePath(Routes.NATURE_INDEX, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                        Natures
                    </Nav.Link>
                    <Nav.Link as={Link}
                              to={generatePath(Routes.ABILITY_INDEX, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                        Abilities
                    </Nav.Link>
                    <Dropdown as={Nav.Item}>
                        <Dropdown.Toggle as={Nav.Link}>Tools</Dropdown.Toggle>
                        <Dropdown.Menu>
                            <Dropdown.Item as={Link}
                                           to={generatePath(Routes.TOOLS_CAPTURE_RATE, {version: (props.currentVersion as ApiRecord.Version).slug})}>
                                Capture Rate
                            </Dropdown.Item>
                        </Dropdown.Menu>
                    </Dropdown>
                </Nav>}
                {!ready && <BsNavbar.Text><Loading uncontained/></BsNavbar.Text>}
                <QuickSearch/>
            </BsNavbar.Collapse>
        </BsNavbar>
    );
}
