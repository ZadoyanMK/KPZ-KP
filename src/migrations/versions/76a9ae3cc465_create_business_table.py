"""create_business_table

Revision ID: 76a9ae3cc465
Revises: 
Create Date: 2018-12-03 15:25:41.278529

"""
from alembic import op
import sqlalchemy as sa
from logging import log


# revision identifiers, used by Alembic.
revision = '76a9ae3cc465'
down_revision = None
branch_labels = None
depends_on = None


def upgrade():
    try:
        op.create_table(
            'businesses',
            sa.Column('id', sa.INTEGER(), primary_key=True, autoincrement=True),
            sa.Column('url', sa.VARCHAR(64), unique=True),
            sa.Column('name', sa.VARCHAR(256)),

            sa.Column('address_locality', sa.VARCHAR(64)),
            sa.Column('address_region', sa.VARCHAR(64)),
            sa.Column('postal_code', sa.VARCHAR(64)),
            sa.Column('street_address', sa.VARCHAR(256)),

            sa.Column('phone', sa.VARCHAR(64)),
            sa.Column('clear_phone', sa.BIGINT(), unique=True),

            sa.Column('fax', sa.VARCHAR(64)),
            sa.Column('website', sa.VARCHAR(512)),
            sa.Column('category', sa.VARCHAR(256)),

            sa.Column('sent_to_customer', sa.DATETIME(), index=True),
            sa.Column('parse_status', sa.INTEGER(), index=True, nullable=False, server_default=sa.text("0")),

            sa.Column('created_at', sa.TIMESTAMP(),server_default=sa.text('CURRENT_TIMESTAMP'), nullable=False),
            sa.Column(
                'updated_at', sa.TIMESTAMP(),
                server_default=sa.text('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'), nullable=False
            ),
        )
    except Exception as err:
        log(50, f'{err}')


def downgrade():
    op.drop_table(
        'businesses'
    )
